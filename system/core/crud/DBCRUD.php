<?php

abstract class DBCRUD
{
	protected $con;
	private $numRowsEffect = 0;
	private $bindWheres = [];
	private $bindUpdates = [];
	private $bindInserts = [];
	private $onJoin = '';
	const IS_NULL = 'IS NULL';
	const IS_NOT_NULL = 'IS NOT NULL';
	const OPERATOR = ['=', '>', '<', '<=', '>=', '!=', 'LIKE', 'IN'];
	const t_integer = ['int', 'tinyint', 'mediumint', 'bigint'];
	const t_text = ['varchar', 'char', 'mediumtext', 'longtext', 'timestamp'];
	const t_decimal = ['float'];

	public function __construct()
	{
		$this->connectDB();
	}

	public function startTransaction() {
		$this->con->begin_transaction();
	}

	public function commit() {
		$this->con->commit();
	}

	public function rollback() {
		$this->con->rollback();
	}

	private function executeQuery($sql, $params) {
		$stmt = $this->con->prepare($sql);

		if($stmt === false) {
			log("$sql / " .$this->con->error);
			$time = date("h:m:s d/m/Y", time());
			if (DEBUG) {
				die("$time : $sql / " .$this->con->error);
			}
			return null;
		}

		// if has bind params
		if (count($params) > 0) {
			$arrBindParams = array();

			//Types: s = string, i = integer, d = double, b = blob 
			$paramType = key($params);
			$arrBindParams[] = &$paramType;

			$paramValues = $params[$paramType];
			for ($i = 0; $i < count($paramValues); $i++) {
				$arrBindParams[] = &$paramValues[$i];
			}

			call_user_func_array(array($stmt, 'bind_param'), $arrBindParams);
		}

		return $stmt;
	}

	public function select($fields = array(), $alias = '') {
		$cols = count($fields) > 0 ? implode(',', $fields) : '*';
		$this->query = "SELECT $cols FROM {$this->table}" . (!empty($alias) ? " AS $alias" : '');
		return $this;
	}

	public function update($data = array(), $where = array()) {
		$this->query = "UPDATE {$this->table} SET ";
		$this->buildUpdate($data)
			->where($where);
		$k1 = key($this->bindUpdates);
		$k2 = key($this->bindWheres);
		$bindParam[$k1 . $k2] = array_merge($this->bindUpdates[$k1], $this->bindWheres[$k2]);
		$stmt = $this->executeQuery($this->query, $bindParam);
		if ( empty($stmt) ) return false;
		$stmt->execute();
		$data = $this->select()->where(array_merge($where, $data))->get();
		return $data;
	}

	public function updateOr($data = array(), $where = array()) {
		$this->query = "UPDATE {$this->table} SET ";
		$this->buildUpdate($data)
			->whereOr($where);
		$k1 = key($this->bindUpdates);
		$k2 = key($this->bindWheres);
		$bindParam[$k1 . $k2] = array_merge($this->bindUpdates[$k1], $this->bindWheres[$k2]);
		$stmt = $this->executeQuery($this->query, $bindParam);
		if ( empty($stmt) ) return false;
		$stmt->execute();
		$data = $this->select()->whereOr(array_merge($where, $data))->get();
		return $data;
	}

	public function save($data = array()) {
		$find = $this->select(['id'])->where($data)->first();
		if (isset($find->id)) {
			throw new Exception("Record exist in database.");
		}
		array_unshift($this->fields, 'id');
		$cols = implode(',', $this->fields);
		

		$typeColumn = $this->getTypeColumn();
		$dataUni = [];
		foreach ($this->fields as $col) {
			$uni = $typeColumn[$col]['uni'];
			if ($uni == 'UNI') {
				$dataUni[$col] = $data[$col];
			}
		}
		$find = $this->select(['id'])->where($dataUni)->first();
		if (isset($find->id)) {
			throw new Exception("Record duplicate in database " . json_encode($dataUni));
		}

		$this->query = "INSERT INTO {$this->table} ($cols) VALUES ";
		$data['created_at'] = current_time();
		$data['updated_at'] = current_time();
		$this->buildInsert($data, $typeColumn);
		$stmt = $this->executeQuery($this->query, $this->bindInserts);
		if ( empty($stmt) ) return false;
		$stmt->execute();
		$stmt->free_result();
		$stmt->close();
		return true;
	}

	public function findOrCreate($data = array()) {
		$find = $this->select()->where($data)->get();
		if (count($find) > 0) {
			return $find;
		}
		$this->save($data);
		$find = $this->select()->where($data)->get();
		return $find;
	}
	
	public function destroy($data = array()) {
		$find = $this->select()->where($data)->get();
		if (count($find) == 0) {
			return false;
		}
		$this->query = "DELETE FROM {$this->table}";
		$this->buildWhere($data);
		$stmt = $this->executeQuery($this->query, $this->bindWheres);
		if ( empty($stmt) ) return false;
		$stmt->execute();
		$stmt->free_result();
		$stmt->close();
		return true;
	}

	public function destroySoft($data = array()) {
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
	public function find($id) {
		$this->query = "SELECT * FROM {$this->table}";
		$this->where([
			'id' => $id
		]);
		$data = $this->get();
		return count($data) > 0 ? $data[0] : arr_to_obj([]);
	}

	public function first() {
		$data = $this->get();
		return count($data) > 0 ? $data[0] : arr_to_obj([]);
	}

	public function last() {
		$data = $this->get();
		$max = count($data);
		return $max > 0 ? $data[$max - 1] :  arr_to_obj([]);
	}

	// su dung voi bind_result
	public function findAll() {
		$this->query = "SELECT * FROM {$this->table}";
		$data = $this->get();
		return $data;
	}

	public function where($fields = array()) {
		$this->bindWheres = [];
		return $this->buildWhere($fields);
	}

	public function whereOr($fields = array()) {
		$this->bindWheres = [];
		return $this->buildWhere($fields, "OR");
	}

	public function whereLike($fields = array()) {
		$this->bindWheres = [];
		$fieldsChange = [];
		foreach ($fields as $k => $v) {
			$kex = explode(':', $k);
			$kf = count($kex) > 1 ? $kex[0] : $k;
			$fieldsChange["$kf:like"] = $v;
		}
		return $this->buildWhere($fieldsChange);
	}

	public function whereLikeOr($fields = array()) {
		$this->bindWheres = [];
		$fieldsChange = [];
		foreach ($fields as $k => $v) {
			$kex = explode(':', $k);
			$kf = count($kex) > 1 ? $kex[0] : $k;
			$fieldsChange["$kf:like"] = $v;
		}
		return $this->buildWhere($fieldsChange, 'OR');
	}

	public function order($cols, $type = 'ASC') {
		$cols = str_replace(' ', '', $cols);
		$this->query .= " ORDER BY $cols $type";
		return $this;
	}
	
	/**
	 * $operator like, =, !=, <=, >=
	 * $condition AND | OR
	 */
	private function buildWhere($fields = array(), $condition = 'AND') {
		$cols = implode(',', $fields);
		$wheres = [];
		$kBind = '';
		$vBind = [];

		foreach ($fields as $col => $value) {
			$colField = explode(':', $col);
			$colName = $colField[0];
			$operator = count($colField) == 2 ? $colField[1] : '=';
			$operator = strtoupper($operator);
			if (empty($colName)) throw new Exception("Column empty.");
			if (!in_array($operator, self::OPERATOR)) throw new Exception("Operator $operator not valid");

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
				} else if (is_numeric($value) || is_bool($value)) {
					$kBind .= 'i';
					$vBind[] = ($operator == 'LIKE') ? "%$value%" : $value;
				} else if (is_float($value)) {
					$kBind .= 'd';
					$vBind[] = ($operator == 'LIKE') ? "%$value%" : $value;
				} else if (is_array($value)) {
					foreach ($value as $v) {
						$kBind .= 'i';
						$vBind[] = $v;
					}
				}
			}
		}
	
		$this->bindWheres[$kBind] = $vBind;
		$this->query .= " WHERE " . implode(" $condition ", $wheres);

		return $this;
	}

	private function buildUpdate($data = array()) {
		$vBind = [];
		$kBind = '';
		foreach ($data as $col => $value) {
			$set[] = "$col = ?";
			$vBind[] = $value;
			if (is_string($value)) {
				$kBind .= 's';
			} else if (is_numeric($value) || is_bool($value)) {
				$kBind .= 'i';
			} else if (is_float($value)) {
				$kBind .= 'd';
			}
		}
		$this->bindUpdates[$kBind] = $vBind;
		$this->query .= implode(",", $set);
		return $this;
	}

	private function buildInsert($data = array(), $typeColumn) {
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
				if (in_array($type, self::t_integer)) {
					$kBind .= 'i';
				} else if (in_array($type, self::t_text)) {
					$kBind .= 's';
				} else if (in_array($type, self::t_decimal)) {
					$kBind .= 'd';
				}
				$vBind[] = $value;
			} else if ($col == 'id') {
				$kBind .= 'i';
				$vBind[] =  null;
			} else {
				if (in_array($type, self::t_integer)) {
					$kBind .= 'i';
					$vBind[] = $isnull == 'NO' ? 0 : null;
				} else if (in_array($type, self::t_text)) {
					$kBind .= 's';
					if ($isnull == 'NO') {
						throw new Exception("$col cannot empty");
					}
					$vBind[] = null;
				} else if (in_array($type, self::t_decimal)) {
					$kBind .= 'd';
					$vBind[] = $isnull == 'NO' ? 0 : null;
				}
			}
		}
		$this->bindInserts[$kBind] = $vBind;
		$this->query .= implode(",", $insert);
		return $this;
	}

	public function get($index = -1) {
		$stmt = $this->executeQuery($this->query, $this->bindWheres);

		if ( empty($stmt) ) return false;
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
			if ($index >= $max ) {
				throw new Exception("The data index exceeds the $max  element array size.");
			}
			return $data[$index];
		}

		return arr_to_obj($data, false);
	}

	public function join($table, $handler, $alias = '') {
		$this->typeJoin = " INNER JOIN ";
		$this->tableJoin = $table;
		call_user_func($handler, $this);

		$this->query .= $this->typeJoin .' '. $this->tableJoin . (!empty($alias) ? " AS $alias" : '') . " ON " . $this->onJoin;
		$this->onJoin = '';
		return $this;
	}

	public function leftJoin($table, $handler, $alias = '') {
		$this->typeJoin = " LEFT JOIN ";
		$this->tableJoin = $table;
		call_user_func($handler, $this);

		$this->query .= $this->typeJoin .' '. $this->tableJoin . (!empty($alias) ? " AS $alias" : '') . " ON " . $this->onJoin;
		$this->onJoin = '';
		return $this;
	}

	public function rightJoin($table, $handler, $alias = '') {
		$this->typeJoin = " RIGHT JOIN ";
		$this->tableJoin = $table;
		call_user_func($handler, $this);

		$this->query .= $this->typeJoin .' '. $this->tableJoin . (!empty($alias) ? " AS $alias" : '') . " ON " . $this->onJoin;
		$this->onJoin = '';
		return $this;
	}

	public function on($col1, $condition, $col2) {
		$this->onJoin .= empty($this->onJoin) ? "$col1 $condition $col2" : " AND $col1 $condition $col2";
		return $this;
	}

	public function onOr($col1, $condition, $col2) {
		$this->onJoin .= empty($this->onJoin) ? "$col1 $condition $col2" : " OR $col1 $condition $col2";
		return $this;
	}

	public function getNumRowsEffect() {
		return $this->numRowsEffect;
	}

	private function getTypeColumn($table = '') {
		$table = empty($table) ? $this->table : $table;
		$fields = implode(',', array_map(function($el) {
			return "'$el'";
		},  $this->fields));
		$stmt = $this->executeQuery("SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_KEY FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '".DB_NAME."' AND TABLE_NAME = '$table' AND COLUMN_NAME in($fields)", []);
		$stmt->execute();
        $data = array();
		$results = $stmt->get_result();
		while($row = $results->fetch_assoc()){
			$data[$row['COLUMN_NAME']] = [
				'type' => $row['DATA_TYPE'],
				'isnull' => $row['IS_NULLABLE'],
				'uni' => $row['COLUMN_KEY']
			];
		}
		$stmt->free_result();
		$stmt->close();
		return $data;
	}

	private function connectDB() {
		$this->con = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
		$this->con->set_charset("utf8");
		if ( mysqli_connect_errno() ) {
			$error = mysqli_connect_error();
			log($error);
			if (DEBUG) {
				die($error);
			}
		} 
	}

	private function disconnectDB() {
		$this->con->close();
	}
}
