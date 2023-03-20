<?php

// router site
Router::site(function() {
	Router::group("/auth", function($pf) {
		Router::post("$pf/doLogin", 'AuthController@doLogin', 'doLogin');
		Router::get("$pf/doLogout", 'AuthController@doLogout', 'doLogout');
		Router::post("$pf/doRegistry", 'AuthController@doRegistry', 'doRegistry');
	});

	Router::group("/gift", function($pf) {
		Router::post("$pf/turn", 'GiftController@updateTurn', 'updateTurn');
		Router::post("$pf/hisspin", 'GiftController@popupHistorySpin', 'popupHistorySpin');
		Router::post("$pf/hisgift", 'GiftController@popupHistoryGift', 'popupHistoryGift');
		Router::post("$pf/change", 'GiftController@giftChange', 'giftChange');
		Router::post("$pf/addturn", 'GiftController@addTurnShare', 'addTurnShare');
	});

	
	Router::get('/', 'HomeController@index', 'home');
});
