<?php

class CookieApp
{
	private static $arrTimeFormat = [
			'minute' => 'mi',
			'hour' => 'h',
			'day' => 'd',
			'month' => 'm'
		];

    public static function set($name, $value, $time) {
    	preg_match('#([0-9]+)([a-zA-Z]+)#', $time, $matchs);

        $timeFormat = $matchs[2];

    	if ( strlen($timeFormat) > 2 || !in_array($timeFormat, self::$arrTimeFormat) ) {
    		throw new Exception('Time format does not exist.');	
    	}

    	$number = $matchs[1];// time

    	switch ($timeFormat) {
    		case self::$arrTimeFormat['minute']:
    			$timeExpire = time() + $number * 60; // 60s
    			break;
    		case self::$arrTimeFormat['hour']:
    			$timeExpire = time() + $number * 3600; // 60 * 60s
    			break;
    		case self::$arrTimeFormat['day']:
    			$timeExpire = time() + $number * 86400; // 24 * 3600s
    			break;
    		case self::$arrTimeFormat['month']:
    			$timeExpire = time() + $number * 2592000; // 30 days * 86400s
    			break;
    		default:
    			$timeExpire = time() + 24 * 3600; // 1 day
    			break;
    	}
    	setcookie($name, $value, $timeExpire, '/');
    }

    public static function remove($name, $path = '/') {
    	setcookie($name, null, -1, $path);
    }

    public static function get($name) {
        return isset($_COOKIE[$name]) ? $_COOKIE[$name] : '';
    }

	public static function setToken($value) {
        self::set('_token', $value, TIME_EXPIRE_TOKEN);
    }

	public static function getToken() {
        return self::get('_token');
    }
	
}