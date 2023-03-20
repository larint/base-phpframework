<?php
/**
 * SystemRouter
 * @filesource  system/SystemRouter.php
 * @description router system
 */
class SystemRouter{

    private static $routes = [
        'GET'    => [],
        'POST'   => [],
        'ANY'    => [],
        'PUT'    => [],
        'DELETE' => [],
    ];

    private static $patterns = [
        'l'    => '[a-zA-Z0-9\-]+',          // slug
        'a'    => '[a-zA-Z0-9]+',            // alphabel
        'i'    => '[0-9]+',                  // number
        's'    => '[a-zA-Z]+',               // string
        'f'    => '[a-zA-Z0-9]+\.[a-zA-Z]+', // file with extension, .html .php
    ];

    private static $requestKey = 'request';
    private static $handlerKey = 'handler';
    private static $aliasRouteKey = 'alias';

    private static $request = REQUEST_SITE; // check is a site or admin request

    const REGVAL = '/({.+?})/';
    const METHOD_NOT_FOUND = 1;
    const ROUTER_NOT_FOUND = 2;
    
    public static function site($handler){
        self::$request = REQUEST_SITE;
        call_user_func($handler);
    }

    public static function admin($pathPrefix, $handler){
        if ( substr($pathPrefix, 0, 1) != '/' ) {
            throw new Exception('router name without the "/" character at the beginning');
        }
        self::$request = REQUEST_ADMIN;
        call_user_func($handler, $pathPrefix);
    }

    public static function group($pathPrefix, $handler){
        if ( substr($pathPrefix, 0, 1) != '/' ) {
            throw new Exception('router name without the "/" character at the beginning');
        }
        call_user_func($handler, $pathPrefix);
    }

    public static function action($path, $handler) {
        // get the closest group name
        $lastElePath = self::lastElePath($path, 1);

        self::get($path, $handler . '@index', $lastElePath . '.index');
        self::get($path . '/create', $handler . '@create', $lastElePath . '.create');
        self::get($path . '/show/{id:i}', $handler . '@show', $lastElePath . '.show');
        self::get($path . '/edit/{id:i}', $handler . '@edit', $lastElePath . '.edit');
        self::post($path . '/store', $handler . '@store', $lastElePath . '.store');
        self::put($path . '/update', $handler . '@update', $lastElePath . '.update');
        self::delete($path . '/delete', $handler . '@delete', $lastElePath . '.delete');
        self::get($path . '/delete/{id:i}', $handler . '@delete', $lastElePath . '.delete_get');
    }

    public static function any($path, $handler, $alias = null){
        self::addRoute('ANY', $path, $handler, $alias);
    }

    public static function get($path, $handler, $alias = null){
        self::addRoute('GET', $path, $handler, $alias);
    }

    public static function post($path, $handler, $alias = null){
        self::addRoute('POST', $path, $handler, $alias);
    }

    public static function put($path, $handler, $alias = null){
        self::addRoute('PUT', $path, $handler, $alias);
    }

    public static function delete($path, $handler, $alias = null){
        self::addRoute('DELETE', $path, $handler, $alias);
    }

    private static function addRoute($method, $path, $handler, $alias){
        array_push(self::$routes[$method], [$path => [self::$handlerKey => $handler, self::$requestKey => self::$request, self::$aliasRouteKey => $alias ]]);
    }

    public static function run() { 
        // check if the token is valid
        self::validateTokenPOST();

        $requestMethod = self::getRequest();
        $requestUri = self::delLastSlashUri($_GET['url']);

        // if the method doesn't exist
        if ( empty($requestMethod) || !in_array($requestMethod, array_keys(self::$routes)) ) {
            return self::METHOD_NOT_FOUND;
        }

        foreach (self::$routes[$requestMethod]  as $resource) {
            $args = []; 
            $routeName = key($resource); 
            $handler = reset($resource[$routeName]); 

            if( preg_match(self::REGVAL, $routeName) ){
                list($args, $uri, $routeName) = self::getInfoRouter($requestUri, $routeName);  
            }

            if( !preg_match("#^$routeName$#", $requestUri) ){
                //unset(self::$routes[$requestMethod]);
                continue;
            }
            
            if ( in_array($requestMethod, array_diff(array_keys(self::$routes), ['GET'])) ) {
                $args = isset($_POST) ? json_decode(json_encode($_POST)) : null;
            }
            // action
            if( is_string($handler) && strpos($handler, '@') ){
                list($controller, $action) = explode('@', $handler); 
                return ['controller' => $controller, 'action' => $action, 'args' => $args];
            }

            if( empty($args) ){
                return $handler();
            }

            return call_user_func_array($handler, $args);
        }

        return self::ROUTER_NOT_FOUND;
    }

    public static function name($alias, $params = array()) {
        foreach (self::$routes as $keyMethod => $method) {
            foreach ($method as $route) {
                $routeName = key($route);
                $aliasRoute = $route[$routeName][self::$aliasRouteKey];
                if ( $alias == $aliasRoute ) {
                    if ( self::isRouteParams($routeName) ) {
                        $keyPattern = array_keys(self::$patterns);
                        $i = 0;
                        $routeWithParam = preg_replace_callback(self::REGVAL, function($matches) use($params, &$i) {
                            return $params[$i++];
                        }, $routeName);
                        return ROOT_URL . $routeWithParam;
                    } else {
                        if ( count($params) > 0 ) {
                            throw new Exception($routeName . " no parameters required");
                        }
                        return ROOT_URL . $routeName;
                    }
                }
            }
        }
        
        return ROOT_URL;
    }

    private static function isRouteParams($routeName) {
        if ( preg_match("#^(.*){.*}(.*)$#", $routeName) ) {
            return true;
        }
        return false;
    }

    private static function getInfoRouter($requestUri, $routeName){
        $routeWithReg = self::parseRegexRouter($routeName);

        $regUri = explode('/', $routeName);
        $regReal = array_replace($regUri, explode('/', $requestUri) );
        $args = array_diff( $regReal, $regUri );
        $nameArgs = array_diff( $regUri, $regReal );

        $arrKeyArgs = array();
        if( preg_match("#^$routeWithReg$#", $requestUri) ){
            foreach ($nameArgs as $value) {
                $key = array_search ($value, $regUri);
                $paramReg = ltrim($value, '{');
                $name = explode(':', $paramReg)[0];
                $arrKeyArgs[$name] = $args[$key];
            }
        }
        
        return [$arrKeyArgs, $routeName, $routeWithReg];
    }

    private static function parseRegexRouter($routeName){
        $routeWithReg = preg_replace_callback(self::REGVAL, function($matches) {
            $patterns = self::$patterns;
            $matches[0] = str_replace(['{', '}'], '', $matches[0]);
            $pattern = explode(':', $matches[0])[1];
            if( in_array($pattern, array_keys($patterns)) ){
                return $patterns[$pattern];
            }

        }, $routeName);
        return $routeWithReg;
    }

    public static function isRequestAdmin() {
        $requestMethod = self::getRequest();
        $requestUri = self::delLastSlashUri($_GET['url']);
        
        if ( empty($requestMethod) || !in_array($requestMethod, array_keys(self::$routes)) ) {
            return false;
        }

        foreach (self::$routes[$requestMethod]  as $resource) {
            $routeName = key($resource);
            $request = $resource[$routeName]['request'];
            $routeWithReg = '';
            if( preg_match(self::REGVAL, $routeName) ) {
                $routeWithReg = self::parseRegexRouter($routeName);
            }

            if ( preg_match("#^$routeWithReg$#", $requestUri) || $requestUri == $routeName) {
                return $request == REQUEST_ADMIN;
            }
        }
        return false;
    }

    private static function validateTokenPOST() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
   
        if ($requestMethod == 'POST') {
            if ( !isset($_POST['_token']) || empty($_POST['_token']) || !isset($_COOKIE['_token']) ) {
                self::redirect('tokenExpired');
            }
        }
        return true;
    }

    private static function getRequest() {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if ( $requestMethod == 'POST' && isset($_POST['_method']) ) {
            $method = strtoupper($_POST['_method']);
            $requestMethod = ( $method == 'DELETE' || $method == 'PUT' ) ? $method : null;
        }

        return $requestMethod;
    }

    private static function delLastSlashUri($path) {
        return strlen($path) > 1 ? rtrim($path, '/') :$path;
    }

    public static function redirect($nameRoute, $msg = array(), $params = array(), $statusCode = 303) {
        if ( !empty($msg) ) {
            SessionApp::setMSG($msg);
        }
        
        $route = self::name($nameRoute, $params);
        header('Location: ' . $route, false, $statusCode);
        die();
    }

    public static function redirectBack($msg = array()) {
        if ( is_array($msg) && count($msg) > 0 ) {
            $type = key($msg);
            SessionApp::setMSG($msg[$type], $type);
            SessionApp::setTypeMSG($msg);
        }
        
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        die();
    }

    public static function lastElePath($url, $index) {
        $url = rtrim($url, '/');
        $tokens = explode('/', $url);
        return $tokens[sizeof($tokens)-$index];
    }
}