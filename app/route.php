<?php
use AppRouter as Router;

// router site
Router::site(function() {
	Router::get('/', 'HomeController@index', 'home', ['auth']);

	Router::group("/auth", function() {
		Router::post("/doLogin", 'AuthController@doLogin', 'doLogin');
		Router::get("/doLogout", 'AuthController@doLogout', 'doLogout');
		Router::post("/doRegistry", 'AuthController@doRegistry', 'doRegistry');
		Router::get("/getRegistry", 'AuthController@getRegistry', 'getRegistry');
	});
	

	Router::get('/query/{id:i}/edit/{name:s}', 'HomeController@pageQuery', 'pageQuery');
	Router::get('/read-data', 'HomeController@readData', 'readData');
});
