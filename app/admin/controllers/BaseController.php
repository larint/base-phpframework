<?php

abstract class BaseController extends AppInit

{
	public function __construct() 
	{
		parent::__construct(REQUEST_SITE);
	}

	public function __destruct() {

	}
}