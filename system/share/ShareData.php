<?php
use SessionApp as Data;

class ShareData {
	
	function __construct()
	{
		
	}

	public function get($key) {
		return Data::getShareData()[$key];
	}

}