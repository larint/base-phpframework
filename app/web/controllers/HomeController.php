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
        $this->view->render('pages.index', compact('title'));
    }

    public function pageQuery($request)
    {
        $params = $request;   
        $this->view->render('pages.query_string', compact('params'));
    }

    public function readData($request)
    {
        $accounts = $this->account->findAll();
        // dd($accounts);
        $this->view->render('pages.read_data', compact('accounts'));
    }
    
}