<?php

use AppRouter as Router;

Router::admin("/admin", function () {
    Router::get("/", 'HomeController@index', 'admin');
});
