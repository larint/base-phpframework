<?php

class ViewComposer extends ViewData
{

    public function pass()
    {
        $account = new Account;
        $role = new Role;
        $account = $account->select()->findAll();
        $role = $role->select()->findAll();

        $this->passData([
            'pages.index', 
            'pages.sign_up'
        ], compact('account'));

        $this->passData([
            'pages.index', 
            'pages.query_string'
        ], compact('role'));
    
    }

}