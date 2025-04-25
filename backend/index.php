<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/routes/UserRoutes.php';
//require __DIR__ . '/app/services/UserService.php';

Flight::route('/', function(){
    echo 'Hello, World!';
});

Flight::start();

