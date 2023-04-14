<?php

class MiddlewareLoader
{

    public function __construct($request)
    {
        $this->request = $request;
        $this->pathMiddleware = ($request == REQUEST_ADMIN) ? PATH_ADMIN : PATH_SITE;
    }

    public function load() {
		if ($this->request == REQUEST_SYSTEM) return true;
        include_once PATH_SYSTEM . '/core/middleware/Middleware.php';
    	$files = glob($this->pathMiddleware . '/middleware/*.php');

        foreach ($files as $fileName) {
            $objName = basename($fileName, '.php');
            include_once $fileName;
        }

        return true;
    }
}