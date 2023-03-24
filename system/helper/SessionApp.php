<?php

class SessionApp
{
	private static $success = 'success';
	private static $danger = 'danger';

    public static function set($key, $value) {
		$_SESSION[$key] = $value;
	}

	public static function get($key) {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : '';
	}

	public static function has($key) {
		return isset($_SESSION[$key]) && !empty($_SESSION[$key]);
	}

	public static function remove($key) {
		unset($_SESSION[$key]);
	}

	public static function all() {
		return $_SESSION;
	}

	public static function removeMsg() {
		self::remove('error');
		self::remove('msg');
		self::remove('type_msg');
		self::remove('share_data');
	}

	public static function setMSG($value, $key = 'msg') {
		self::set($key, $value);
	}

	public static function getMSG($key) {
		$msg = self::get($key);
		return empty($msg) ? [] : $msg;
	}

	public static function getTypeMSG() {
		$type = self::get('type_msg');
		if ($type == 'error') {
			return self::$danger;
		}
		return $type;
	}

	public static function has_error($name) {
		$msg = self::getMSG('error');
		return  ( !empty($msg) && array_key_exists($name, $msg) ) ? true : false;
	}

	public static function setTypeMSG($type) {
		$t = is_array($type) ? $type['type'] : $type;
		self::set('type_msg', $t);
	}

	public static function setShareData($value) {
		self::set('share_data', $value);
	}

	public static function getShareData() {
		$msg = self::get('share_data');
		return empty($msg) ? [] : $msg;
	}

	public static function setUser($value){
		self::set('user', $value);
	}

	public static function user(){
		return self::get('user');
	}

	public static function removeUser(){
		self::remove('user');
	}

	public static function action($value = ''){
		if (empty($value)) {
			$action = self::get('action');
			self::remove('action');
			return $action;
		}
		self::set('action', $value);
	}
}