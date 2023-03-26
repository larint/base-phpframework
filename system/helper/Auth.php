<?php

class Auth
{

	public static function set($value){
		SessionApp::set('user', $value);
	}

	public static function user(){
		return SessionApp::get('user');
	}

	public static function unset(){
		SessionApp::remove('user');
	}

}