<?php

/**

 * BaseController

 * @filesource  system/core/controllers/BaseController.php

 */

class BaseController extends AppInit
{
	public function __construct($request = '') 
	{
		parent::__construct($request ? $request : REQUEST_SYSTEM);
	}

	public function __destruct() {

	}
}