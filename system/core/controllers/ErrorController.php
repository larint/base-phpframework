<?php

class ErrorController extends BaseController
{
	public function notFound()
    {
        $view = 'error.404';
        $path = str_replace('.', '/', $view );
        $pathView = PATH_VENDOR_VIEW . "/$path.php";
        if (file_exists($pathView)) {
            return $this->view->renderAny($pathView);
        }
        return $this->view->render($view);
    }
}