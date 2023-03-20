<?php 
/**
 * App
 * @filesource  apps/App.php
 */
class Router extends SystemRouter
{

    public function __construct()
    {

    }

    public static function run()
    {
        $runs = SystemRouter::run();

        if ( $runs == SystemRouter::METHOD_NOT_FOUND || $runs == SystemRouter::ROUTER_NOT_FOUND || is_array($runs) ) {
            if ( is_array($runs) ) {
                $controller = $runs['controller'];
                $action = $runs['action'];
                $args = $runs['args'];
            } else {
                $controller = explode('@', ERROR_CONTROLLER)[0];
                $action = explode('@', ERROR_CONTROLLER)[1];
            }
            // Include controller 
            include_once PATH_SYSTEM . '/core/LoaderController.php';
            $pathApp = SystemRouter::isRequestAdmin() ? PATH_ADMIN  : PATH_SITE;
            include_once $pathApp . '/controllers/BaseController.php';
            include_once $pathApp . '/controllers/' . $controller . '.php';

            if ( !class_exists($controller) ){
                throw new Exception('Class ' . $controller . ' not exists');
            }

            $controllerObject = new $controller;
            
            if ( !method_exists($controllerObject, $action) ) {
                throw new Exception('Action ' . $action . ' not exist in ' . $controller);
            }

            if ( isset($args) && !empty($args) ) {
                $controllerObject->{$action}($args);
            } else {          
                $controllerObject->{$action}();
            }
        }
    }
}