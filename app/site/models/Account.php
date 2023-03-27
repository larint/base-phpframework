<?php

class Account extends Model
{
	protected $table = 'account';
    protected $fields = [
        'name',
        'email',
        'email_verified_at',
        'password_display',
        'password',
        'is_super',
        'remember_token',
        'created_at',
        'updated_at',
		'deleted_at'
    ];

	public function save($fields) {
        $sql = $this->genInsertQuery($bindParams, $fields);
        return $this->onInsert(
            $sql,
            ['ssissssss' => $bindParams]
        );
    }

	public function findOnWhereAccount($username) {
		$data = $this->findOnWhere('s', [
				'loginName:=' => $username
			]);
		if ( !empty($data) && count($data) == 1 ) {
            return $data[0];
        } 
        
        return $data; 
	}

	public function findOnWhereAccountNoPass($username) {
		$data = $this->findOnWhere('s', [
				'loginName:=' => $username
			]);

		if ( !empty($data) && count($data) == 1 ) { 
			$data[0]->password_hash = '';
			$data[0]->pass2 = '';
			return $data[0];
		}

		return $data;
	}

	public function findAccount($id) {
		return $this->find($id);
	}

	public function findAccountNoPass($id) {
		$data = $this->find($id);
		if ( !empty($data) ) {
			$data->ad_password = '';
		}

		return $data;
	}

	public function updateAccountPass($fields = array(), $whereCols = array()) {
		$sql = $this->genUpdateQuery($bindParams, $fields , $whereCols);
		return $this->onUpdate(
			$sql,
            ['si' => $bindParams]
        );
	}

	public function updateAccount($fields = array(), $whereCols = array()) {
		$sql = $this->genUpdateQuery($bindParams, $fields , $whereCols);
		return $this->onUpdate(
			$sql,
            ['ssssi' => $bindParams]
        );
	}

	public function resetLoginFails($username) {
		$sql = $this->genUpdateQuery($bindParams, [
						'ad_login_fail' => 0,
					] , [ 
					'ad_username:=' => $username,
					'ad_email:=' => $username,
					'|'
				]);

		return $this->onUpdate(
			$sql,
            ['iss' => $bindParams]
        );
	}

	public function increaseLoginFails($username) {
		if ( $this->getNumRow() > 0 ) {
			return $this->increment([
						'ad_login_fail' => 'inc:1', // update tang len 1
					] , 'ss', [ 
					'ad_username:=' => $username,
					'ad_email:=' => $username,
					'|'
				]);
		}
	}
}