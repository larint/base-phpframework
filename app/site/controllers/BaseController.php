<?php



/**

 * BaseController

 * @filesource apps/site/controllers/BaseController.php

 */

abstract class BaseController extends LoaderController

{
	public function __construct() 
	{
		parent::__construct(REQUEST_SITE);
	}

	public function __destruct() {

	}
}