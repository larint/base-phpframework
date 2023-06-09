<?php

session_start();

define('APP_ROOT', '');
define('ROOT_URL', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . APP_ROOT);
define('PUBLIC_FOLDER', '/public');
define('URL_PUBLIC', ROOT_URL . '/public');
define('PATH_ROOT', $_SERVER['DOCUMENT_ROOT'] . APP_ROOT);
define('PATH_SYSTEM', PATH_ROOT . '/system');
define('PATH_PUBLIC', PATH_ROOT . '/public');
define('PATH_APP', PATH_ROOT . '/app');
define('PATH_WEB', PATH_APP . '/web');
define('PATH_API', PATH_APP . '/api');
define('PATH_ADMIN', PATH_APP . '/admin');
define('PATH_VIEW_ADMIN', PATH_ADMIN . '/views');
define('PATH_VIEW_SITE', PATH_WEB . '/views');
define('PATH_VIEW_SYSTEM', PATH_SYSTEM . '/core/views');
define('PATH_VENDOR_VIEW', PATH_APP . '/vendor/views');
define('PATH_STORAGE', PATH_SYSTEM . '/storage');
// default controller error used in AppRouter
define('ERROR_CONTROLLER', 'ErrorController@index');

define('REQUEST_ADMIN', 'admin');
define('REQUEST_WEB', 'web');
define('REQUEST_SYSTEM', 'system');
define('REQUEST_API', 'api');

if (!file_exists(PATH_ROOT . '/.env')) {
    dd('.env system configuration file does not exist');
}
Dotenv\Dotenv::createImmutable(PATH_ROOT)->load();

$_SESSION['lang'] = isset($_ENV['LANG']) ? $_ENV['LANG'] : "en";
date_default_timezone_set(isset($_ENV['TIMEZONE']) ? $_ENV['TIMEZONE'] : 'Asia/Ho_Chi_Minh');
define('APP_NAME', isset($_ENV['APP_NAME']) ? $_ENV['APP_NAME'] : "web name");
define('SESSION_NAME', isset($_ENV['SESSION_NAME']) ? $_ENV['SESSION_NAME'] : APP_NAME . 'sess');
define('SESSION_EXPIRE', isset($_ENV['SESSION_EXPIRE']) ? $_ENV['SESSION_EXPIRE'] : 86400);
define('SESSION_DRIVER', isset($_ENV['SESSION_DRIVER']) ? $_ENV['SESSION_DRIVER'] : 'session');
define("DEBUG", isset($_ENV['DEBUG']) ? filter_var($_ENV['DEBUG'], FILTER_VALIDATE_BOOLEAN) : false);
define("TIME_EXPIRE_TOKEN", isset($_ENV['TIME_EXPIRE_TOKEN']) ? $_ENV['TIME_EXPIRE_TOKEN'] : '1h');
define("DB_CONNECTION", isset($_ENV['DB_CONNECTION']) ? $_ENV['DB_CONNECTION'] : "mysql");
define("DB_HOST", isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : "localhost");
define("DB_USER", isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : "root");
define("DB_PASS", isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : "");
define("DB_NAME", isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : "");
define("LOG_QUERY", isset($_ENV['LOG_QUERY']) ? $_ENV['LOG_QUERY'] : false);