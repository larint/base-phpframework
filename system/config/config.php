<?php
session_start();

define('WEB_ROOT', '');
define('ROOT_URL', (!empty($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . WEB_ROOT);
define('URL_PUBLIC', ROOT_URL . '/public');

define('PATH_ROOT', $_SERVER['DOCUMENT_ROOT'] . WEB_ROOT);
define('PATH_SYSTEM', PATH_ROOT . '/system');
define('PATH_PUBLIC', PATH_ROOT . '/public');
define('PATH_APP', PATH_ROOT . '/app');
define('PATH_SITE', PATH_APP . '/site');
define('PATH_ADMIN', PATH_APP . '/admin');
define('VIEW_ADMIN', PATH_ADMIN . '/views');
define('VIEW_SITE', PATH_SITE . '/views');

// default controller error used in AppRouter
define('page404', PATH_ROOT . '/404');
define('ERROR_CONTROLLER', 'ErrorController@index');

define('REQUEST_ADMIN', 'admin');
define('REQUEST_SITE', 'site');

if (!file_exists(PATH_ROOT . '/.env')) dd('.env system configuration file does not exist');
Dotenv\Dotenv::createImmutable(PATH_ROOT)->load();

date_default_timezone_set(isset($_ENV['TIMEZONE']) ? $_ENV['TIMEZONE'] : 'Asia/Ho_Chi_Minh');
define("DEBUG", isset($_ENV['DEBUG']) ? filter_var($_ENV['DEBUG'], FILTER_VALIDATE_BOOLEAN): false);
define("TIME_EXPIRE_TOKEN", isset($_ENV['TIME_EXPIRE_TOKEN']) ? $_ENV['TIME_EXPIRE_TOKEN'] : '1h');
define("DB_HOST", isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : "localhost");
define("DB_USER", isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : "root");
define("DB_PASS", isset($_ENV['DB_PASS']) ? $_ENV['DB_PASS'] : "");
define("DB_NAME", isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : "");
define('WEB_NAME', isset($_ENV['WEB_NAME']) ? $_ENV['WEB_NAME'] : "web name");
