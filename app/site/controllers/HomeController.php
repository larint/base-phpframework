<?php

class HomeController extends BaseController
{

    public function __construct() {
        parent::__construct();
        $this->account = new Account();
    }

    public function index()
    {
        $title = 'HOME PAGE';
        $this->view->render('layout:pages.index', compact('title'));
    }

}