<?php

$debugMode = DEBUG ? 1 : 0;
error_reporting($debugMode);	

function showError() {
	$error = error_get_last();
	if ( !empty($error) ) {
		$time = date("h:m:s d/m/Y", time());
		$error_msg = $error['message'] . $error['file'] .' line: '.$error['line'];
		$path =  PATH_SYSTEM . "/storage/log";
		if (!file_exists($path)) {
			mkdir($path, 0777, true);
		}
		error_log($time.' : '. $error_msg . PHP_EOL, 3, "$path/$filename");
		if ( !DEBUG ) {
			echo '<h1 style="text-align: center;color: #bdbdbd; margin-top: 50px">Sorry! The system could not process your request.</h1>';
		} else {
			die($time.' : '. $error_msg);
		}
	}

}

register_shutdown_function('showError');