<?php
use AppRouter as Router;

// router site
Router::site(function() {
	Router::group("/auth", function($pf) {
		Router::post("$pf/doLogin", 'AuthController@doLogin', 'doLogin');
		Router::get("$pf/doLogout", 'AuthController@doLogout', 'doLogout');
		Router::post("$pf/doRegistry", 'AuthController@doRegistry', 'doRegistry');
	});
	
	Router::get('/', 'HomeController@index', 'home');

	Router::get('/query/{id:i}/edit/{name:s}', 'HomeController@pageQuery', 'pageQuery');

	Router::get('/form', 'HomeController@form', 'form');
});
