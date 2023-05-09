<?php

class HomeController extends BaseController
{

    public function __construct() {
        parent::__construct();
    }

    public function index()
    {
        $this->view->render('pages.index');
    }

    
}