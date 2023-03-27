<?php

abstract class DBCRUD
{
	protected $con;
	private $numRowsEffect = 0;

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

		$arrBindParams = array();

		//Types: s = string, i = integer, d = double, b = blob 
		$paramType = key($params);
		$arrBindParams[] = &$paramType;

		$paramValues = $params[$paramType];
		for ($i = 0; $i < count($paramValues); $i++) {
			$arrBindParams[] = &$paramValues[$i];
		}

		call_user_func_array(array($stmt, 'bind_param'), $arrBindParams);

		return $stmt;
	}

	protected function onSelect($sql, $params = array()) {
		$stmt = $this->executeQuery($sql, $params);
		
		if ( empty($stmt) ) return false;
		$stmt->execute();

		$meta = $stmt->result_metadata(); 
        while ($field = $meta->fetch_field()) { 
            $parameters[] = &${$field->name}; 
            $nameVar[] = $field->name;
        } 
        call_user_func_array(array($stmt, 'bind_result'), $parameters); 
        
        $datas = array();
        while ($stmt->fetch()) {
        	$row = array();
 			foreach ($nameVar as $var_name) {
 				$row[$var_name] = ${$var_name};
 			}
 			$datas[] = $row;
        }

		$this->numRowsEffect = $stmt->num_rows;
		/* free results */
   		$stmt->free_result();
		$stmt->close();

		return $datas;
	}

	protected function onUpdate($sql, $params = array()) {
		$stmt = $this->executeQuery($sql, $params);
		if ( empty($stmt) ) return false;
		$stmt->execute();
		$stmt->close();
		return true;
	}

	protected function onInsert($sql, $params = array()) {
		$stmt = $this->executeQuery($sql, $params);
		if ( empty($stmt) ) return false;
		$stmt->execute();
		$stmt->close();
		return true;
	}

	protected function onDelete($sql, $params) {
		$stmt = $this->executeQuery($sql, $params);
		if ( empty($stmt) ) return false;
		$stmt->execute();
		$stmt->close();
		return true;
	}

	protected function getNumRow() {
		return $this->numRowsEffect;
	}

	protected function toObj($data = array()) {
		return json_decode(json_encode($data));
	}

	protected function genInsertQuery(&$bindParams, $fields = array()) {
		$lastKField = last_key_arr($fields);

		$sql = "insert into $this->table values(null,";
		foreach ($fields as $key => $value) {
			$sql .= ($key != $lastKField) ? '?, ' : '?)';
			$bindParams[] = $value;
		}

		return $sql;
	}

	protected function genSelectQuery(&$bindParams, $whereCols = array(), $sortColumn = array()){
		$sql = "select id,";

		$lastVWhere = end($whereCols);
		if ( strlen($lastVWhere) == (count($whereCols) - 2) && (contain_str('&', $lastVWhere) || contain_str('|', $lastVWhere)) ) {
			$wheres = str_split(end($whereCols));
		}

		$lastCols = end($this->fields);
		foreach ($this->fields as $value) {
			$sql .= ($lastCols != $value) ? $value .',' : $value . " from $this->table";
		}

		$sql .= ' where ';
		if ( isset($wheres) ) {
			array_pop($whereCols);
		} else {
			if ( count($whereCols) == 0) {
				$sql .= '?';
			}
		}
		
		$i=0;
		$lastKWhere = last_key_arr($whereCols);
		foreach ($whereCols as $key => $value) {
			$con = explode(':', $key);
			if ($key != $lastKWhere) {
				$whe = $wheres[$i] == '&' ? ' and ' : ' or ';
				$sql .= $con[0] .' '. $con[1] . ' ? ' . $whe;
			} else {
				$sql .= $con[0] .' '. $con[1] . ' ? ';
			}
			if ($con[1] == 'like') {
				$value = "%$value%";
			}
			$bindParams[] = $value;
			$i++;
		}

		if ( count($whereCols) == 0 ) { 
			$bindParams[] = 1;
		}

		if (count($sortColumn) == 0) {
			$sortField = 'id';
			$sequence  = 'asc';
		} else {
			$sortField = key($sortColumn);
			$sequence  = $sortColumn[$sortField];
		}
		$sql .= " order by $sortField $sequence";

		return $sql;
	}

	protected function genUpdateQuery(&$bindParams, $fields = array(), $whereCols = array()){
		$lastKField = last_key_arr($fields);

		$sql = "update $this->table set ";
		foreach ($fields as $key => $value) {
			$v = explode(':', $value);
			if ( count($v) > 0 && $v[0] == 'inc' ) {
				$v_con = $key .'+?';
				${$key} = $v[1];
			} elseif ( count($v) > 0 && $v[0] == 'dec' ) {
				$v_con = $key .'-?';
				${$key} = $v[1];
			} else {
				$v_con = '?';
				${$key} = $value;
			}

			$bindParams[] = ${$key};

			$sql .= ($key != $lastKField) ? $key ."=$v_con, " : $key ."=$v_con";
		}

		if ( contain_str('&', end($whereCols)) || contain_str('|', end($whereCols)) ) {
			$wheres = str_split(end($whereCols));
		}

		$sql .= ' where ';
		if ( isset($wheres) ) {
			array_pop($whereCols);
		} else {
			if ( count($whereCols) == 0) {
				$sql .= '?';
			}
		}
		
		$i=0;
		$lastKWhere = last_key_arr($whereCols);
		foreach ($whereCols as $key => $value) {
			$con = explode(':', $key);
			if ($key != $lastKWhere) {
				$whe = $wheres[$i] == '&' ? ' and ' : ' or ';
				$sql .= $con[0] .$con[1]. '?' . $whe;
			} else {
				$sql .= $con[0] .$con[1]. '?';
			}

			$bindParams[] = $value;
			$i++;
		}

		if ( count($whereCols) == 0 ) { 
			$bindParams[] = 1;
		}

		return $sql;
	}

	protected function total($matchTypeData = 'i', $whereCols = array()) {
		$sql = "select count(*) as total_record from $this->table";

		if ( contain_str('&', end($whereCols)) || contain_str('|', end($whereCols)) ) {
			$wheres = str_split(end($whereCols));
		}

		$sql .= ' where ';
		if ( isset($wheres) ) {
			array_pop($whereCols);
		} else {
			if ( count($whereCols) == 0) {
				$sql .= '?';
			}
		}
		
		$i=0;
		$lastKWhere = last_key_arr($whereCols);
		foreach ($whereCols as $key => $value) {
			$con = explode(':', $key);
			if ($key != $lastKWhere) {
				$whe = $wheres[$i] == '&' ? ' and ' : ' or ';
				$sql .= $con[0] .$con[1]. '?' . $whe;
			} else {
				$sql .= $con[0] .$con[1]. '?';
			}
			$bindParams[] = $value;
			$i++;
		}

		if ( count($whereCols) == 0 ) { 
			$bindParams[] = 1;
		}

		$data = $this->onSelect(
            $sql,
            [$matchTypeData => $bindParams]
        );

		if ( !is_bool($data) && count($data) == 1 ) {
        	return arr_to_obj($data[0])->total_record;
        }

        return 0;
	}

	// su dung voi bind_result
	protected function find($id) {
		$sql = $this->genSelectQuery($bindParams, [
				'id:=' => $id
			]);
		$data = $this->onSelect(
			$sql,
            ['i' => $bindParams]
        );
		
        if ( !is_bool($data) && count($data) == 1 ) {
        	return arr_to_obj($data[0]);	
        } 

        return null;
	}

	// su dung voi bind_result
	protected function findAll() {
		$sql = $this->genSelectQuery($bindParams, []);
		$data = $this->onSelect(
			$sql,
            ['i' => [1]]
        );

        if ( !is_bool($data) && count($data) > 0 ) {
        	return arr_to_obj($data);	
        } 

        return [];
	}

	protected function first() {
		$sql = $this->genSelectQuery($bindParams);
		$sql .= " limit ?,?";

		$data = $this->onSelect(
			$sql,
            ['iii' => [1,0,1]]
        );

        if ( !is_bool($data) && count($data) == 1 ) {
            return arr_to_obj($data[0]);
        }

        return null;
    }

    protected function max($col) {
		$data = $this->onSelect(
			"select max($col) as $col from $this->table where ?", 
			['i' => [1]]
		);

		if ( !is_bool($data) && count($data) == 1) {
            return arr_to_obj($data[0])->{$col};
        }

        return -1;
	}

	protected function selectRandom($limit) {
		$data = $this->onSelect(
			"select * from $this->table order by RAND() limit ?", 
			['i' => [$limit]]
		);

		if ( !$data || count($data) == 0 ) {
        	return null;
        }

        return arr_to_obj($data);
	}
	
	protected function select($sql, $bindParams = array()) {
		$matchTypeData = key($bindParams);
		$data = $this->onSelect(
			$sql,
            [$matchTypeData => $bindParams[$matchTypeData]] 
        );
		
        if ( !$data || count($data) == 0 ) {
        	return [];
        }
        
		return arr_to_obj($data);
	}

	protected function update($sql, $bindParams = array()) {
		$matchTypeData = key($bindParams);
		return $this->onUpdate(
			$sql,
            [$matchTypeData => $bindParams]
        );
	}

	protected function findOnWhere($matchTypeData = 'i', $whereCols = array(), $sortColumn = array()) {
		$sql = $this->genSelectQuery($bindParams, $whereCols, $sortColumn);
		
		$data = $this->onSelect(
			$sql,
            [$matchTypeData => $bindParams] 
        );
		
        if ( !$data || count($data) == 0 ) {
        	return [];
        }
        
		return arr_to_obj($data);
	}

	protected function existRecord($matchTypeData = null, $whereCols = array()) {
		if (empty($matchTypeData)) {
			$find = $this->findOnWhere();	
		} else {
			$find = $this->findOnWhere($matchTypeData, $whereCols);	
		}

		return empty($find) ? false : true;
	}

	protected function deleteRecord($id) {
        return $this->onDelete(
            "delete from $this->table where id = ?", 
            ['i' => [$id]]
        );
    }

    public function deleteLogicRecord($id) {
        $sql = $this->genUpdateQuery($bindParams, [
        			'delete_record' => 1
        		] , [
        			'id:=' => $id
        		]);
        return $this->onUpdate(
            $sql,
            ['ii' => $bindParams]
        );
    }

    protected function paginateQuery($sql, $whereLimit = array()) {
		$bindParams[] = count($whereLimit) > 0 ? $whereLimit['from'] : 0;
		$bindParams[] = count($whereLimit) > 0 ? $whereLimit['limit'] : 10000;

        $data = $this->onSelect(
            $sql, 
            ['ii' => $bindParams]
        );
        
		if ( !$data || count($data) == 0 ) {
            return [];
        }
        
        return arr_to_obj($data);
    }

	protected function paginate($whereLimit = array(), $sortColumn = array()) {
		$sql = $this->genSelectQuery($bindParams, [], $sortColumn);
		$sql .= " limit ?,?";

		$bindParams[] = count($whereLimit) > 0 ? $whereLimit['from'] : 0;
		$bindParams[] = count($whereLimit) > 0 ? $whereLimit['limit'] : 10000;

        $data = $this->onSelect(
            $sql, 
            ['iii' => $bindParams]
        );
        
		if ( !$data || count($data) == 0 ) {
            return [];
        }
        
        return arr_to_obj($data);
    }

    protected function increment($fields = array(), $matchTypeData = null, $whereCols = array()) {
    	$sql = $this->genUpdateQuery($bindParams, $fields , $whereCols);

    	if ( empty($matchTypeData) ) {
    		$matchType = '';
    		foreach ($bindParams as $value) {
    			$matchType .= 'i';
			}
    	} else {
    		$matchType = '';
    		foreach ($fields as $value) {
    			$matchType .= 'i';
			}
    		$matchType .= $matchTypeData;
    	}
    	
		return $this->onUpdate(
			$sql,
            [$matchType => $bindParams]
        );
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
