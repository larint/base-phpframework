<?php

class TokenController extends BaseController
{

    public function tokenExpired()
    {
        $this->view->render('token_expired');
    }

}