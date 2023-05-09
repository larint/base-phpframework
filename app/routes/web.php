<?php

use AppRouter as Router;

// router site
Router::site(function () {
    Router::get("/", 'HomeController@index', 'home', ['Authenticated']);

    Router::group("/auth", function () {
        Router::get("/", 'AuthController@getLogin', 'auth');
        Router::get("/getLogin", 'AuthController@getLogin', 'getLogin', ['RedirectIfAuthenticated']);
        Router::post("/doLogin", 'AuthController@doLogin', 'doLogin');
        Router::get("/doLogout", 'AuthController@doLogout', 'doLogout');
        Router::post("/doRegistry", 'AuthController@doRegistry', 'doRegistry');
        Router::get("/getRegistry", 'AuthController@getRegistry', 'getRegistry');
    });


    Router::get('/query/{id:i}/edit/{name:s}', 'HomeController@pageQuery', 'pageQuery', ['Authenticated']);
    Router::get('/read-data', 'HomeController@readData', 'readData', ['Authenticated']);
});
