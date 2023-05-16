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
    public const T_NUMERIC = ['int', 'tinyint', 'smallint', 'mediumint', 'bigint'];
    public const T_STRING = ['varchar', 'char', 'mediumtext', 'longtext', 'text', 'timestamp'];
    public const T_DECIMAL = ['float', 'decimal', 'double', 'real'];
    public const T_DATETIME = ['datetime', 'timestamp'];

    public function __construct()
    {
        $this->connectDB();
    }

    public function startTransaction()
    {
        $this->conn->begin_transaction();
    }

    public function commit()
    {
        $this->conn->commit();
    }

    public function rollback()
    {
        $this->conn->rollback();
    }

    private function executeQuery($sql, $params)
    {
        $stmt = $this->conn->prepare($sql);

        if (LOG_QUERY) {
            log_db($sql);
        }
        if ($stmt === false) {
            log_db("$sql / " .$this->conn->error);
            $time = date("h:m:s d/m/Y", time());
            if (DEBUG) {
                die("$time : $sql / " .$this->conn->error);
            }
            return null;
        }

        // if has bind params
        if (count($params) > 0) {
            $arrBindParams = array();

            $paramType = key($params);
            $arrBindParams[] = &$paramType;

            $paramValues = $params[$paramType];
            for ($i = 0; $i < count($paramValues); $i++) {
                $arrBindParams[] = &$paramValues[$i];
            }

            call_user_func_array(array($stmt, 'bind_param'), $arrBindParams);
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

    public function limit($offset = 0, $limit = 10)
    {
        if ($offset < 0) {
            throw new Exception("Parameter \$offset in 'limit' method must be greater than 0", 1);
        }
        $this->query .= " LIMIT $offset, $limit";
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
        $stmt->execute();
        $data = $this->select()->where(array_merge($where, $data))->get();
        return $data;
    }

    public function updateOr($data = array(), $where = array())
    {
        $this->query = "UPDATE {$this->table} SET ";
        $this->buildUpdate($data)
            ->whereOr($where);
        $k1 = key($this->bindUpdates);
        $k2 = key($this->bindWheres);
        $bindParam[$k1 . $k2] = array_merge($this->bindUpdates[$k1], $this->bindWheres[$k2]);
        $stmt = $this->executeQuery($this->query, $bindParam);
        if (empty($stmt)) {
            return false;
        }
        $stmt->execute();
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

        $this->query = "INSERT INTO {$this->table} ($cols) VALUES ";
        $this->buildInsert($data, $typeColumn);
        $stmt = $this->executeQuery($this->query, $this->bindInserts);
        if (empty($stmt)) {
            return false;
        }
        $stmt->execute();
        $stmt->free_result();
        $stmt->close();
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

    public function createBulk($data = array())
    {
        foreach ($data as $row) {
            $this->create($row);
        }
        return true;
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
        $stmt->execute();
        $stmt->free_result();
        $stmt->close();
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

    public function count($fields = array())
    {
        if (count($fields) > 0) {
            return $this->select(['COUNT(*) AS c'])
                        ->where($fields)
                        ->first()->c;
        }
        return $this->select(['COUNT(*) AS c'])->first()->c;
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
        try {
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
        } catch (\Exception $e) {
            log_db($e->getMessage());
        }

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
                $value = $data[$col];
                if (in_array($type, self::T_NUMERIC)) {
                    $kBind .= 'i';
                } elseif (in_array($type, self::T_STRING)) {
                    $kBind .= 's';
                } elseif (in_array($type, self::T_DECIMAL)) {
                    $kBind .= 'd';
                } elseif (in_array($type, self::T_DATETIME)) {
                    $kBind .= 's';
                } else {
                    $kBind .= 's';
                }
                $vBind[] = $value;
            } elseif ($col == 'id') {
                $kBind .= 'i';
                $vBind[] =  null;
            } else {
                if ($isnull == 'NO') {
                    throw new Exception("$col cannot empty");
                }
                if (in_array($type, self::T_NUMERIC)) {
                    $kBind .= 'i';
                    $vBind[] = $isnull == 'NO' ? 0 : null;
                } elseif (in_array($type, self::T_STRING)) {
                    $kBind .= 's';
                    $vBind[] = null;
                } elseif (in_array($type, self::T_DECIMAL)) {
                    $kBind .= 'd';
                    $vBind[] = $isnull == 'NO' ? 0 : null;
                } elseif (in_array($type, self::T_DATETIME)) {
                    $kBind .= 's';
                    $vBind[] = null;
                } else {
                    $kBind .= 's';
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
        $stmt->execute();

        $meta = $stmt->result_metadata();
        while ($field = $meta->fetch_field()) {
            $parameters[] = &${$field->name};
            $nameVar[] = $field->name;
        }
        call_user_func_array(array($stmt, 'bind_result'), $parameters);

        $data = array();
        while ($stmt->fetch()) {
            $row = array();
            foreach ($nameVar as $var_name) {
                $row[$var_name] = ${$var_name};
            }
            $data[] = $row;
        }

        $this->numRowsEffect = $stmt->num_rows;
        /* free results */
        $stmt->free_result();
        $stmt->close();
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

    public function paginate($page = 1, $limit = 10, $range = 3)
    {
        $numrows = $this->count();

        $totalPages = ceil($numrows / $limit);

        // get the current page or set a default
        $currentPage = $page;

        // if current page is greater than total pages...
        if ($currentPage > $totalPages) {
            $currentPage = $totalPages;
        }
        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $currentUrl = current_url(true);

        // the offset of the list, based on current page
        $from = ($page - 1) * $limit;

        // get the info from the db
        $data = $this->select()->limit($from, $limit)->get();

        /******  build the pagination links ******/
        $htmlLink = '';
        // if not on page 1, don't show back links
        if ($currentPage > 1) {
            $htmlLink .= " <a href='$currentUrl?page=1'><<</a> ";
            // get previous page num
            $prevpage = $currentPage - 1;
            $htmlLink .= " <a href='$currentUrl?page=$prevpage'><</a> ";
        }

        // loop to show links to range of pages around current page
        for ($x = ($currentPage - $range); $x < (($currentPage + $range) + 1); $x++) {
            // if it's a valid page number...
            if (($x > 0) && ($x <= $totalPages)) {
                // if we're on current page...
                if ($x == $currentPage) {
                    // 'highlight' it but don't make a link
                    $htmlLink .= "<a href='#' class='active'>$x</a> ";

                } else {
                    $htmlLink .= " <a href='$currentUrl?page=$x'>$x</a> ";
                }
            }
        }

        // if not on last page, show forward and last page links
        if ($currentPage != $totalPages) {
            // get next page
            $nextpage = $currentPage + 1;
            // echo forward link for next page
            $htmlLink .= " <a href='$currentUrl?page=$nextpage'>></a> ";
            // echo forward link for lastpage
            $htmlLink .= " <a href='$currentUrl?page=$totalPages'>>></a> ";
        }
        $htmlLink = '<div class="pagination">'.$htmlLink.'</div>';
        return arr_to_obj([
            'data' => $data,
            'link' => $htmlLink
        ]);
    }

    private function getTypeColumn($table = '')
    {
        $table = empty($table) ? $this->table : $table;
        $typeColumns = $this->select(['COLUMN_NAME', 'DATA_TYPE', 'IS_NULLABLE', 'COLUMN_KEY'], '', 'information_schema.COLUMNS')
                ->where([
                    'TABLE_SCHEMA' => DB_NAME,
                    'TABLE_NAME' => $table,
                    'COLUMN_NAME' => $this->fields
                ])->get();

        $data = array();
        foreach ($typeColumns as $item) {
            $data[$item->COLUMN_NAME] = [
                'type' => $item->DATA_TYPE,
                'isnull' => $item->IS_NULLABLE,
                'uni' =>$item->COLUMN_KEY
            ];
        }
        return $data;
    }

    private function connectDB()
    {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->conn->set_charset("utf8");
        if (mysqli_connect_errno()) {
            $error = mysqli_connect_error();
            log_error($error);
            if (DEBUG) {
                die($error);
            }
        }
    }

    private function disconnectDB()
    {
        $this->conn->close();
    }
}
