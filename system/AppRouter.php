<?php
/**
 * AppRouter
 * @filesource  system/AppRouter.php
 * @description router system
 */
class AppRouter
{
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

    private static $controller = 'controller';
    private static $requestKey = 'request';
    private static $handlerKey = 'handler';
    private static $aliasRouteKey = 'alias';
    private static $middleware = 'middleware';
    private static $action = 'action';
    private static $args = 'args';
    private static $pathPrefix = '';

    private static $request = REQUEST_WEB; // check is a site or admin request
    public const DEFAULT_CONTROLLERS = ['TokenController', 'ErrorController'];
    public const REGVAL = '/({.+?})/';
    public const METHOD_NOT_FOUND = 1;
    public const ROUTER_NOT_FOUND = 2;

    public static function admin($pathPrefix, $handler)
    {
        self::$request = REQUEST_ADMIN;
        self::group($pathPrefix, $handler);
    }

    public static function web($handler)
    {
        self::$request = REQUEST_WEB;
        call_user_func($handler);
    }

    public static function api($handler)
    {
        self::$pathPrefix = '/api';
        self::$request = REQUEST_API;
        call_user_func($handler);
    }

    public static function group($pathPrefix, $handler)
    {
        if (substr($pathPrefix, 0, 1) != '/') {
            throw new Exception('Router name without the "/" character at the beginning');
        }
        self::$pathPrefix .= $pathPrefix;
        call_user_func($handler);
        // get parent path
        $basePath = dirname(self::$pathPrefix);
        self::$pathPrefix = $basePath == '/' ? '' : $basePath;
    }

    public static function prefix($pathPrefix, $handler)
    {
        self::group($pathPrefix, $handler);
    }

    public static function action($path, $handler, $middleware = array())
    {
        // get the closest group name
        $lastElePath = self::lastElePath($path, 1);

        self::get($path, "$handler@index", "$lastElePath.index", $middleware);
        self::get("$path/create", "$handler@create", "$lastElePath.create", $middleware);
        self::get("$path/show/{id:i}", "$handler@show", "$lastElePath.show", $middleware);
        self::get("$path/edit/{id:i}", "$handler@edit", "$lastElePath.edit", $middleware);
        self::post("$path/store", "$handler@store", "$lastElePath.store", $middleware);
        self::put("$path/update", "$handler@update", "$lastElePath.update", $middleware);
        self::delete("$path/delete", "$handler@delete", "$lastElePath.delete", $middleware);
        self::get("$path/delete/{id:i}", "$handler@delete", "$lastElePath.delete_get", $middleware);
    }

    public static function any($path, $handler, $alias = null, $middleware = array())
    {
        self::addRoute('ANY', $path, $handler, $alias, $middleware);
    }

    public static function get($path, $handler, $alias = null, $middleware = array())
    {
        self::addRoute('GET', $path, $handler, $alias, $middleware);
    }

    public static function post($path, $handler, $alias = null, $middleware = array())
    {
        self::addRoute('POST', $path, $handler, $alias, $middleware);
    }

    public static function put($path, $handler, $alias = null, $middleware = array())
    {
        self::addRoute('PUT', $path, $handler, $alias, $middleware);
    }

    public static function delete($path, $handler, $alias = null, $middleware = array())
    {
        self::addRoute('DELETE', $path, $handler, $alias, $middleware);
    }

    private static function addRoute($method, $path, $handler, $alias, $middleware = array())
    {
        $path = self::$pathPrefix ? self::$pathPrefix . $path : $path;
        $path = $path == '/' ? $path : rtrim($path, '/');
        array_push(
            self::$routes[$method],
            [
                $path => [
                    self::$handlerKey => $handler,
                    self::$requestKey => self::$request,
                    self::$aliasRouteKey => $alias,
                    self::$middleware => $middleware,
                ]
            ]
        );
    }

    private static function filterControler()
    {
        // check if the token is valid
        self::validateToken();

        $requestMethod = self::getRequest();
        $requestUri = self::delLastSlashUri($_GET['url']);

        // if the method doesn't exist
        if (empty($requestMethod) || !in_array($requestMethod, array_keys(self::$routes))) {
            return self::METHOD_NOT_FOUND;
        }

        foreach (self::$routes[$requestMethod] as $resource) {
            $args = [];
            $routeName = key($resource);
            $handler = $resource[$routeName][self::$handlerKey];
            $middleware = $resource[$routeName][self::$middleware];
            $requestFrom = $resource[$routeName][self::$requestKey];
            if(preg_match(self::REGVAL, $routeName)) {
                list($args, $uri, $routeName) = self::getInfoRouter($requestUri, $routeName);
            }

            if(!preg_match("#^$routeName$#", $requestUri)) {
                //unset(self::$routes[$requestMethod]);
                continue;
            }

            if (in_array($requestMethod, array_diff(array_keys(self::$routes), ['GET']))) {
                $args = isset($_POST) ? json_decode(json_encode($_POST, JSON_FORCE_OBJECT)) : null;
            }
            // merge query and args
            $queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
            if ($queryString) {
                $querieParames = array();
                parse_str($queryString, $querieParames);
                $args = array_merge($args, $querieParames);
            }

            // action
            if(is_string($handler) && strpos($handler, '@')) {
                list($controller, $action) = explode('@', $handler);
                // $args = json_decode(json_encode($args, JSON_FORCE_OBJECT)); // format to object
                return [
                    self::$controller => $controller,
                    self::$action => $action,
                    self::$args => $args,
                    self::$middleware => $middleware,
                    self::$requestKey => $requestFrom
                ];
            }

            if(is_callable($handler)) {
                // $args = json_decode(json_encode($args, JSON_FORCE_OBJECT)); // format to object
                return [
                    self::$controller => null,
                    self::$action => $handler,
                    self::$args => $args,
                    self::$middleware => $middleware,
                    self::$requestKey => $requestFrom
                ];
            }

            return call_user_func_array($handler, $args);
        }

        return self::ROUTER_NOT_FOUND;
    }

    public static function run()
    {
        self::setDefaultRouter();
        $runs = self::filterControler();

        if ($runs == self::METHOD_NOT_FOUND || $runs == self::ROUTER_NOT_FOUND || is_array($runs)) {
            if (is_array($runs)) {
                $controller = $runs[self::$controller];
                $action = $runs[self::$action];
                $args = $runs[self::$args];
                $middleware = $runs[self::$middleware];
                $requestFrom = $runs[self::$requestKey];
            } else {
                $controller = 'ErrorController';
                $action = 'notFound';
                $args = [];
                $middleware = [];
                $requestFrom = null;
            }

            if ($requestFrom == REQUEST_ADMIN) {
                $pathApp = PATH_ADMIN;
            } elseif ($requestFrom == REQUEST_WEB) {
                $pathApp = PATH_WEB;
            } elseif ($requestFrom == REQUEST_API) {
                $pathApp = PATH_API;
            }

            // Include controller
            include_once PATH_SYSTEM . '/core/AppInit.php';

            if (is_callable($action)) {
                include_once PATH_SYSTEM . "/core/controllers/BaseController.php";
                $baseController = new BaseController(self::$request);

                // save action for post request
                self::savePostRequest($action, $args);

                return !empty($args) ? $action($args) : $action();
            }

            if (in_array($controller, self::DEFAULT_CONTROLLERS)) {
                include_once PATH_SYSTEM . "/core/controllers/BaseController.php";
                include_once PATH_SYSTEM . "/core/controllers/$controller.php";
            } else {
                include_once "$pathApp/controllers/BaseController.php";
                include_once "$pathApp/controllers/$controller.php";
            }

            if (!class_exists($controller)) {
                throw new Exception('Class ' . $controller . ' not exists');
            }

            $controllerObject = new $controller();

            if (!method_exists($controllerObject, $action)) {
                throw new Exception("Action $action not exist in $controller");
            }

            foreach ($middleware as $middleClass) {
                if (!class_exists($middleClass)) {
                    throw new Exception('Middleware name ' . $middleClass . ' not exists');
                }
                $middleObj = new $middleClass();
                $middleObj->handle($args);
            }

            // save action for post request
            self::savePostRequest($action, $args);

            // init ValidateRequest
            include_once PATH_SYSTEM . "/core/request/ValidateRequest.php";
            $validateRequest = new ValidateRequest($args);
            $controllerObject->{$action}($validateRequest);

        }
    }

    /**
     * save action for post request
     */
    private static function savePostRequest($action, $args)
    {
        // save action for post request
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if (in_array($requestMethod, ['POST', 'PUT', 'DELETE'])) {
            SessionApp::action($action);
            if (isset($args) && !empty($args)) {
                // save post data when submit form
                SessionApp::setPostRequest((array)$args);
            }
        }
    }

    public static function name($alias, $params = array())
    {
        foreach (self::$routes as $keyMethod => $method) {
            foreach ($method as $route) {
                $routeName = key($route);
                $aliasRoute = $route[$routeName][self::$aliasRouteKey];
                if ($alias == $aliasRoute) {
                    if (self::isRouteParams($routeName)) {
                        if (!is_array($params)) {
                            throw new Exception("Parameter must be array.");
                        }
                        if (count($params) == 0) {
                            throw new Exception("$routeName has not declared the parameters passed to the router.");
                        }
                        preg_match_all(self::REGVAL, $routeName, $matchArgs);
                        if (count($matchArgs[0]) != count($params)) {
                            throw new Exception("Router $routeName requires ".count($matchArgs[0])." parameters to pass.");
                        }
                        $i = 0;
                        $routeWithParam = preg_replace_callback(self::REGVAL, function ($matches) use ($params, &$i) {
                            if (isset($matches[0])) {
                                $typeParam = explode(':', trim($matches[0], "{}"))[1];
                                $pattern = self::$patterns[$typeParam];
                                preg_match("/$pattern/", $params[$i], $mArg);
                                if (count($mArg) == 0) {
                                    throw new Exception("Invalid parameter type ". $matches[0]);
                                }
                                return $params[$i++];
                            }
                            throw new Exception("Invalid parameter type ". $matches[0]);
                        }, $routeName);
                        return ROOT_URL . $routeWithParam;
                    } else {
                        if (count($params) > 0) {
                            throw new Exception("Router $routeName no parameters required");
                        }
                        return ROOT_URL . $routeName;
                    }
                }
            }
        }

        throw new Exception("Router name not define");
    }

    private static function isRouteParams($routeName)
    {
        if (preg_match("#^(.*){.*}(.*)$#", $routeName)) {
            return true;
        }
        return false;
    }

    private static function getInfoRouter($requestUri, $routeName)
    {
        $routeWithReg = self::parseRegexRouter($routeName);
        $regUri = explode('/', $routeName);
        $regReal = array_replace($regUri, explode('/', $requestUri));
        $args = array_diff($regReal, $regUri);
        $nameArgs = array_diff($regUri, $regReal);

        $arrKeyArgs = array();
        if(preg_match("#^$routeWithReg$#", $requestUri)) {
            foreach ($nameArgs as $value) {
                $key = array_search($value, $regUri);
                $paramReg = ltrim($value, '{');
                $name = explode(':', $paramReg)[0];
                $arrKeyArgs[$name] = $args[$key];
            }
        }

        return [$arrKeyArgs, $routeName, $routeWithReg];
    }

    private static function parseRegexRouter($routeName)
    {
        $routeWithReg = preg_replace_callback(self::REGVAL, function ($matches) {
            $matches[0] = str_replace(['{', '}'], '', $matches[0]);
            $pattern = explode(':', $matches[0])[1];
            if(in_array($pattern, array_keys(self::$patterns))) {
                return self::$patterns[$pattern];
            }

        }, $routeName);
        return $routeWithReg;
    }

    private static function validateToken()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];

        if (in_array($requestMethod, ['POST', 'PUT', 'DELETE'])) {
            if (!isset($_POST['_token']) || empty($_POST['_token']) || !isset($_COOKIE['_token']) || $_COOKIE['_token'] != $_POST['_token']) {
                self::redirectRoute('tokenExpired');
            }
        }
        return true;
    }

    private static function getRequest()
    {
        $requestMethod = $_SERVER['REQUEST_METHOD'];
        if ($requestMethod == 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
            $requestMethod = ($method == 'DELETE' || $method == 'PUT') ? $method : null;
        }

        return $requestMethod;
    }

    private static function delLastSlashUri($path)
    {
        return strlen($path) > 1 ? rtrim($path, '/') : $path;
    }

    private static function redirectRoute($nameRoute, $msg = array(), $params = array(), $statusCode = 303)
    {
        if (!empty($msg)) {
            SessionApp::setMSG($msg);
        }

        $route = AppRouter::name($nameRoute, $params);
        header('Location: ' . $route, false, $statusCode);
        die();
    }

    public static function lastElePath($url, $index)
    {
        $url = rtrim($url, '/');
        $tokens = explode('/', $url);
        return $tokens[sizeof($tokens)-$index];
    }

    private static function setDefaultRouter()
    {
        self::get('/token-expired', 'TokenController@tokenExpired', 'tokenExpired');
        self::get('/notfound', 'ErrorController@notFound', 'notFound');
    }

}
