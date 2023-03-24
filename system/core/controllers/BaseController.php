<?php

/**

 * BaseController

 * @filesource  system/core/controllers/BaseController.php

 */

abstract class BaseController extends AppInit

{
	public function __construct() 
	{
		parent::__construct(REQUEST_SYSTEM);
	}

	public function __destruct() {

	}
}