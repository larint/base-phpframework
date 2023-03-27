<?php

class TokenController extends BaseController
{

    public function tokenExpired()
    {
        $view = 'token_expired';
        $pathView = PATH_VENDOR_VIEW . "/$view.php";
        if (file_exists($pathView)) {
            return $this->view->renderAny($pathView);
        }
        return $this->view->render($view);
    }

}