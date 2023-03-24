<?php
use AppRouter as App;

// load vendor
require dirname(__DIR__).'/vendor/autoload.php';

// config system
require_once dirname(__DIR__) . '/system/config/config.php';

// debug
require_once PATH_SYSTEM . '/Debug.php';

// init router app
require_once PATH_SYSTEM . '/AppRouter.php';

// load route app
require_once PATH_APP . '/route.php';

App::run();