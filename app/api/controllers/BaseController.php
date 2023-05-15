<?php

/**

 * BaseController

 * @filesource apps/admin/controllers/BaseController.php

 */

abstract class BaseController extends AppInit
{
    public function __construct()
    {
        parent::__construct(REQUEST_API);
    }

    public function __destruct()
    {

    }
}
