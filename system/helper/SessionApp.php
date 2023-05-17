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
		$action = self::action();
		self::remove('action');
		self::remove('share_data');
		self::remove('post_request');
		self::remove($action);
	}

	public static function setMSG($value) {
		$action = self::action();
		self::set($action, $value);
	}

	public static function getMSG($key) {
		$msg = self::get($key);
		return empty($msg) ? [] : $msg;
	}

	public static function setPostRequest($value) {
		unset($value['password']);
		self::set('post_request', $value);
	}

	public static function getPostRequest() {
		return self::get('post_request');
	}

	public static function error() {
		$action = self::action();
		$msg = self::get($action);
		return isset($msg['error']) ? $msg['error'] : '';
	}

	public static function action($value = ''){
		if (empty($value)) {
			$action = self::get('action');
			return $action;
		}
		self::set('action', $value);
	}
}