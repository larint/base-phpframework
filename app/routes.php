<?php

// router site
Router::site(function() {
	Router::group("/auth", function($pf) {
		Router::post("$pf/doLogin", 'AuthController@doLogin', 'doLogin');
		Router::get("$pf/doLogout", 'AuthController@doLogout', 'doLogout');
		Router::post("$pf/doRegistry", 'AuthController@doRegistry', 'doRegistry');
	});
	
	Router::get('/', 'HomeController@index', 'home');
});
