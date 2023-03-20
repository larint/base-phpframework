<?php

class URL
{
	public static function redirectBack() {
		header('Location: ' . $_SERVER['HTTP_REFERER']);
		die();
	}

	public static function redirect($url, $statusCode = 303)
	{
		header('Location: ' . $url, false, $statusCode);
		die();
	}

	public static function current() {
		return $_SERVER['REQUEST_URI'];
	}
}