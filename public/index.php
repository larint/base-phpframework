<?php
use Router as App;

// load vendor
require dirname(__DIR__).'/vendor/autoload.php';

// config system
require_once dirname(__DIR__) . '/system/config/config.php';

// debug
require_once PATH_SYSTEM . '/Debug.php';

// load router
require_once PATH_SYSTEM . '/SystemRouter.php';

// controller
require_once PATH_APP . '/Router.php';

// route app
require_once PATH_APP . '/routes.php';

App::run();