<?php

class ErrorController extends BaseController
{
	public function index()
    {
        $this->view->render('error.404');
    }
}