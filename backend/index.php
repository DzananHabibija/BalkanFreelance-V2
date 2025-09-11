<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/routes/MiddlewareRoutes.php';
require __DIR__ . '/app/routes/UserRoutes.php';
require __DIR__ . '/app/routes/GigRoutes.php';
require __DIR__ . '/app/routes/AuthRoutes.php';
require __DIR__ . '/app/routes/CategoryRoutes.php';
require __DIR__ . '/app/routes/BlogRoutes.php';
require __DIR__ . '/app/routes/FavoriteRoutes.php';
//require __DIR__ . '/app/services/UserService.php';

Flight::route('/', function(){
    echo 'Hello, World!';
});

Flight::start();

