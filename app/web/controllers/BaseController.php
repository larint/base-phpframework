<?php

/**

 * BaseController

 * @filesource apps/site/controllers/BaseController.php

 */

abstract class BaseController extends AppInit
{
    public function __construct()
    {
        parent::__construct(REQUEST_WEB);
    }

    public function __destruct()
    {

    }
}
