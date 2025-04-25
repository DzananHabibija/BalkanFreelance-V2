<?php

require_once __DIR__ . '/../services/UserService.php';

Flight::set("user_service",new UserService());

Flight::route('GET /users/@user_id', function ($id) {
    $service = Flight::get("user_service");
    $user = $service->get_user_by_id($id);
    Flight::json($user);
});

Flight::route('POST /register', function() {
    echo "Registering user...";
});

Flight::route('GET /users', function() {
    $service = Flight::get("user_service");
    $users = $service->get_users();
    Flight::json($users);
});


Flight::route('POST /users/add', function() {

    $payload = Flight::request()->data->getData();
    
    //  if($payload['username'] == NULL || $payload['username'] == '') {
         
    //      Flight::halt(500,"Username field is missing");
    //  }
    
    
     $user = Flight::get('user_service')->add_user($payload);
    
   
    
    
    Flight::json($user);
    
    });

    Flight::route('POST /otp/qr-code', function () {
        echo "Generating QR code...";
    });

    Flight::route('POST /otp/verify', function () {
        echo "Verifying OTP...";
    });
    
    
Flight::route('DELETE /users/delete/@user_id', function ($id) {
    $service = Flight::get("user_service");
    $user = $service->delete_user($id);
    Flight::json(["message" => "You have successfully delelted the user!"]);
});

    
Flight::route('GET /connection-check', function(){

    $dao = new UserDao();
    echo "Connected successfully!";
});