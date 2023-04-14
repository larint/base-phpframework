<?php

class Auth
{

	public static function set($value){
		SessionApp::set('user', $value);
		setcookie(SESSION_NAME, $value, time() + SESSION_EXPIRE, "/"); // 86400 = 1 day
	}

	public static function user(){
		return SessionApp::get('user');
	}

	public static function unset(){
		SessionApp::remove('user');
	}

}