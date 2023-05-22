<?php

abstract class DBCRUD
{
    protected $con;
    private $numRowsEffect = 0;
    private $bindWheres = [];
    private $bindUpdates = [];
    private $bindInserts = [];
    private $onJoin = '';
    public const IS_NULL = 'IS NULL';
    public const IS_NOT_NULL = 'IS NOT NULL';
    public const OPERATOR = ['=', '>', '<', '<=', '>=', '!=', 'LIKE', 'IN'];
    public const T_NUMERIC  = ['bit', 'tinyint', 'smallint', 'int', 'bigint', 'decimal', 'numeric', 'smallmoney', 'money', 'float', 'real'];
    public const T_STRING  = ['char', 'varchar', 'text', 'nchar', 'nvarchar', 'ntext', 'binary', 'varbinary', 'image'];
    public const T_DATE  = ['datetime', 'datetime2', 'smalldatetime', 'date', 'time', 'datetimeoffset', 'timestamp'];
    private static $conn = null;

    public function __construct()
    {
        $this->connectDB();
    }

    public function startTransaction()
    {
        if (sqlsrv_begin_transaction(self::$conn) === false) {
            log_error(sqlsrv_errors(), 'begin transaction');
            if (DEBUG) {
                die(print_r(sqlsrv_errors(), true));
            }
        }
    }

    public function commit()
    {
        sqlsrv_commit(self::$conn);
    }

    public function rollback()
    {
        sqlsrv_rollback(self::$conn);
    }

    private function executeQuery($sql, $params)
    {
        // if has bind params
        if (count($params) > 0) {
            $arrBindParams = array();

            $paramType = key($params);
            $paramValues = $params[$paramType];

            for ($i = 0; $i < count($paramValues); $i++) {
                $arrBindParams[] = &$paramValues[$i];
            }

            $stmt = sqlsrv_prepare(self::$conn, $sql, $arrBindParams);
        } else {
            $stmt = sqlsrv_prepare(self::$conn, $sql, []);
        }

        if(!$stmt) {
            log_db(sqlsrv_errors(), "$sql / ");
            if (DEBUG) {
                die(print_r(sqlsrv_errors(), true));
            }
            return null;
        }
        $this->bindWheres = []; // reset params after execute query
        return $stmt;
    }

    public function select($fields = array(), $alias = '', $table = '')
    {
        $cols = count($fields) > 0 ? implode(',', $fields) : '*';
        $table = empty($table) ? $this->table : $table;
        $this->query = "SELECT $cols FROM {$table}" . (!empty($alias) ? " AS $alias" : '');
        return $this;
    }

    public function update($data = array(), $where = array())
    {
        $this->query = "UPDATE {$this->table} SET ";
        $this->buildUpdate($data)->where($where);
        $k1 = key($this->bindUpdates);
        $k2 = key($this->bindWheres);
        $bindParam[$k1 . $k2] = array_merge($this->bindUpdates[$k1], $this->bindWheres[$k2]);
        $stmt = $this->executeQuery($this->query, $bindParam);
        if (empty($stmt)) {
            return false;
        }
        if(sqlsrv_execute($stmt) === false) {
            log_db(sqlsrv_errors(), "update: ");
            if (DEBUG) {
                die(print_r(sqlsrv_errors(), true));
            }
            return false;
        }
        $data = $this->select()->where(array_merge($where, $data))->get();
        return $data;
    }

    public function updateOr($data = array(), $where = array())
    {
        $this->query = "UPDATE {$this->table} SET ";
        $this->buildUpdate($data)->whereOr($where);
        $k1 = key($this->bindUpdates);
        $k2 = key($this->bindWheres);
        $bindParam[$k1 . $k2] = array_merge($this->bindUpdates[$k1], $this->bindWheres[$k2]);
        $stmt = $this->executeQuery($this->query, $bindParam);
        if (empty($stmt)) {
            return false;
        }
        if(sqlsrv_execute($stmt) === false) {
            log_db(sqlsrv_errors(), "updateOr: ");
            if (DEBUG) {
                die(print_r(sqlsrv_errors(), true));
            }
            return false;
        }
        $data = $this->select()->whereOr(array_merge($where, $data))->get();
        return $data;
    }

    public function create($data = array())
    {
        $data['created_at'] = current_time();
        $data['updated_at'] = current_time();
        if (!in_array('id', $this->fields)) {
            array_unshift($this->fields, 'id');
        }
        $cols = implode(',', $this->fields);

        $typeColumn = $this->getTypeColumn();
        $dataUni = [];
        foreach ($this->fields as $col) {
            $uni = $typeColumn[$col]['uni'];
            if ($uni == 'UNI') {
                $dataUni[$col] = $data[$col];
            }
        }
        if (count($dataUni) > 0) {
            $findUni = $this->select(['id'])->where($dataUni)->first();
            if (isset($findUni->id)) {
                throw new Exception("Record duplicate in database " . json_encode($dataUni));
            }
        }
        $max = $this->select(['max(id) as id'])->first();
        $data = ['id' => $max->id + 1] + $data;
        $this->query = "INSERT INTO {$this->table} ($cols) VALUES ";
        $this->buildInsert($data, $typeColumn);
        $stmt = $this->executeQuery($this->query, $this->bindInserts);
        if (empty($stmt)) {
            return false;
        }
        if(sqlsrv_execute($stmt) === false) {
            log_db(sqlsrv_errors(), "save: ");
            if (DEBUG) {
                die(print_r(sqlsrv_errors(), true));
            }
            return false;
        }
        sqlsrv_free_stmt($stmt);
        return true;
    }

    public function findOrCreate($data = array(), $checkFields = array())
    {
        $find = $this->select()->where($checkFields)->get();
        if (count($find) > 0) {
            return $find;
        }
        $this->create($data);
        $find = $this->select()->where($data)->get();
        return $find;
    }

    public function destroy($data = array())
    {
        $find = $this->select()->where($data)->get();
        if (count($find) == 0) {
            return false;
        }
        $this->query = "DELETE FROM {$this->table}";
        $this->buildWhere($data);
        $stmt = $this->executeQuery($this->query, $this->bindWheres);
        if (empty($stmt)) {
            return false;
        }
        if(sqlsrv_execute($stmt) === false) {
            log_db(sqlsrv_errors(), "save: ");
            if (DEBUG) {
                die(print_r(sqlsrv_errors(), true));
            }
            return false;
        }
        sqlsrv_free_stmt($stmt);
        return true;
    }

    public function destroySoft($data = array())
    {
        $find = $this->select()->where($data)->get();
        if (count($find) == 0) {
            return false;
        }
        $this->update([
            'deleted_at' => current_time()
        ], $data);
        return true;
    }

    // su dung voi bind_result
    public function find($id)
    {
        $this->select()
            ->where([
            'id' => $id
        ]);
        $data = $this->get();
        return count($data) > 0 ? $data[0] : null;
    }

    public function first()
    {
        $data = $this->get();
        return count($data) > 0 ? $data[0] : null;
    }

    public function last()
    {
        $data = $this->get();
        $max = count($data);
        return $max > 0 ? $data[$max - 1] : null;
    }

    // su dung voi bind_result
    public function findAll()
    {
        return $this->select()->get();
    }

    public function where($fields = array())
    {
        $this->bindWheres = [];
        return $this->buildWhere($fields);
    }

    public function whereOr($fields = array())
    {
        $this->bindWheres = [];
        return $this->buildWhere($fields, "OR");
    }

    public function whereLike($fields = array())
    {
        $this->bindWheres = [];
        $fieldsChange = [];
        foreach ($fields as $k => $v) {
            $kex = explode(':', $k);
            $kf = count($kex) > 1 ? $kex[0] : $k;
            $fieldsChange["$kf:like"] = $v;
        }
        return $this->buildWhere($fieldsChange);
    }

    public function whereLikeOr($fields = array())
    {
        $this->bindWheres = [];
        $fieldsChange = [];
        foreach ($fields as $k => $v) {
            $kex = explode(':', $k);
            $kf = count($kex) > 1 ? $kex[0] : $k;
            $fieldsChange["$kf:like"] = $v;
        }
        return $this->buildWhere($fieldsChange, 'OR');
    }

    public function order($cols, $type = 'ASC')
    {
        $cols = str_replace(' ', '', $cols);
        $this->query .= " ORDER BY $cols $type";
        return $this;
    }

    /**
     * $operator like, =, !=, <=, >=
     * $condition AND | OR
     */
    private function buildWhere($fields = array(), $condition = 'AND')
    {
        $wheres = [];
        $kBind = '';
        $vBind = [];

        foreach ($fields as $col => $value) {
            $colField = explode(':', $col);
            $colName = $colField[0];
            $operator = count($colField) == 2 ? $colField[1] : '=';
            $operator = strtoupper($operator);
            if (empty($colName)) {
                throw new Exception("Column empty.");
            }
            if (!in_array($operator, self::OPERATOR)) {
                throw new Exception("Operator $operator not valid");
            }

            if (is_string($value) && in_array($value, [self::IS_NULL, self::IS_NOT_NULL])) {
                $wheres[] = "$colName $value";
            } else {
                if (is_array($value)) {
                    $inQuery = implode(',', array_fill(0, count($value), '?'));
                    $wheres[] = "$colName IN ($inQuery)";
                } else {
                    $wheres[] = "$colName $operator ?";
                }

                if (is_string($value)) {
                    $kBind .= 's';
                    $vBind[] = ($operator == 'LIKE') ? "%$value%" : $value;
                } elseif (is_numeric($value) || is_bool($value)) {
                    $kBind .= 'i';
                    $vBind[] = ($operator == 'LIKE') ? "%$value%" : $value;
                } elseif (is_float($value)) {
                    $kBind .= 'd';
                    $vBind[] = ($operator == 'LIKE') ? "%$value%" : $value;
                } elseif (is_array($value)) {
                    foreach ($value as $v) {
                        if (is_string($v)) {
                            $kBind .= 's';
                        } elseif (is_numeric($v) || is_bool($v)) {
                            $kBind .= 'i';
                        } elseif (is_float($v)) {
                            $kBind .= 'd';
                        }
                        $vBind[] = $v;
                    }
                }
            }
        }

        $this->bindWheres[$kBind] = $vBind;
        $this->query .= count($fields) > 0 ? " WHERE " . implode(" $condition ", $wheres) : '';
        return $this;
    }

    private function buildUpdate($data = array())
    {
        $vBind = [];
        $kBind = '';
        foreach ($data as $col => $value) {
            $set[] = "$col = ?";
            $vBind[] = $value;
            if (is_string($value)) {
                $kBind .= 's';
            } elseif (is_numeric($value) || is_bool($value)) {
                $kBind .= 'i';
            } elseif (is_float($value)) {
                $kBind .= 'd';
            }
        }
        $this->bindUpdates[$kBind] = $vBind;
        $this->query .= implode(",", $set);
        return $this;
    }

    private function buildInsert($data = array(), $typeColumn = array())
    {
        $vBind = [];
        $kBind = '';
        $bindv = array_fill(0, count($this->fields), "?");
        $insert[] = "(" . implode(',', $bindv) . ")";

        foreach ($this->fields as $col) {
            $type = $typeColumn[$col]['type'];
            $isnull = $typeColumn[$col]['isnull'];
            $uni = $typeColumn[$col]['uni'];
            if (isset($data[$col])) {
                $kBind .= 's';
                $vBind[] = $data[$col];
            } elseif ($col == 'id') {
                $kBind .= 's';
                $vBind[] =  null;
            } else {
                if ($isnull == 'NO') {
                    throw new Exception("$col cannot empty");
                }
                $kBind .= 's';
                if (in_array($type, self::T_NUMERIC)) {
                    $vBind[] = $isnull == 'NO' ? 0 : null;
                } else {
                    $vBind[] = null;
                }
            }
        }
        $this->bindInserts[$kBind] = $vBind;
        $this->query .= implode(",", $insert);
        return $this;
    }

    public function get($index = -1)
    {
        $stmt = $this->executeQuery($this->query, $this->bindWheres);

        if (empty($stmt)) {
            return arr_to_obj([], false);
        }
        if(sqlsrv_execute($stmt) === false) {
            log_db(sqlsrv_errors(), "Execute: ");
            if (DEBUG) {
                die(print_r(sqlsrv_errors(), true));
            }
            return arr_to_obj([], false);
        }
        $data = array();
        while ($a = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $a;
        }

        $rows_affected = sqlsrv_rows_affected($stmt);
        $this->numRowsEffect = $rows_affected >= 0 ? $rows_affected : 0;
        /* free results */
        sqlsrv_free_stmt($stmt);
        $max = count($data);
        if ($index >= 0) {
            if ($index >= $max) {
                throw new Exception("The data index exceeds the $max  element array size.");
            }
            return $data[$index];
        }

        return arr_to_obj($data, false);
    }

    public function join($table, $handler, $alias = '')
    {
        $this->typeJoin = " INNER JOIN ";
        $this->tableJoin = $table;
        call_user_func($handler, $this);

        $this->query .= $this->typeJoin .' '. $this->tableJoin . (!empty($alias) ? " AS $alias" : '') . " ON " . $this->onJoin;
        $this->onJoin = '';
        return $this;
    }

    public function leftJoin($table, $handler, $alias = '')
    {
        $this->typeJoin = " LEFT JOIN ";
        $this->tableJoin = $table;
        call_user_func($handler, $this);

        $this->query .= $this->typeJoin .' '. $this->tableJoin . (!empty($alias) ? " AS $alias" : '') . " ON " . $this->onJoin;
        $this->onJoin = '';
        return $this;
    }

    public function rightJoin($table, $handler, $alias = '')
    {
        $this->typeJoin = " RIGHT JOIN ";
        $this->tableJoin = $table;
        call_user_func($handler, $this);

        $this->query .= $this->typeJoin .' '. $this->tableJoin . (!empty($alias) ? " AS $alias" : '') . " ON " . $this->onJoin;
        $this->onJoin = '';
        return $this;
    }

    public function on($col1, $condition, $col2)
    {
        $this->onJoin .= empty($this->onJoin) ? "$col1 $condition $col2" : " AND $col1 $condition $col2";
        return $this;
    }

    public function onOr($col1, $condition, $col2)
    {
        $this->onJoin .= empty($this->onJoin) ? "$col1 $condition $col2" : " OR $col1 $condition $col2";
        return $this;
    }

    public function getNumRowsEffect()
    {
        return $this->numRowsEffect;
    }

    private function getTypeColumn($table = '')
    {
        $table = empty($table) ? $this->table : $table;
        $typeColumns = $this->select(['COLUMN_NAME', 'DATA_TYPE', 'IS_NULLABLE'], '', 'information_schema.COLUMNS')
                ->where([
                    'TABLE_CATALOG' => DB_NAME,
                    'TABLE_NAME' => $table,
                    'COLUMN_NAME' => $this->fields
                ])->get();

        $data = array();
        foreach ($typeColumns as $item) {
            $data[$item->COLUMN_NAME] = [
                'type' => $item->DATA_TYPE,
                'isnull' => $item->IS_NULLABLE,
                'uni' => ''
            ];
        }

        // dd($data );

        return $data;
    }

    private function connectDB()
    {
        if (self::$conn == null) {
            self::$conn = sqlsrv_connect(DB_HOST, array(
                "Database"=> DB_NAME,
                "UID"=> DB_USER,
                "PWD"=> DB_PASS
            ));

            if (!self::$conn) {
                log_error(sqlsrv_errors(), "Connection could not be established");
                if (DEBUG) {
                    die(print_r(sqlsrv_errors(), true));
                }
            }
        }
    }

    private function disconnectDB()
    {
        sqlsrv_close(self::$conn);
    }
}
