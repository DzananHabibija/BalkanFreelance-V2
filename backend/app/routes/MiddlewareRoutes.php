<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::route('/*', function () {
    $public_routes = [
        '/auth/register',
        '/auth/login',
        '/auth/logout',
        '/google-login',
        '/google-callback',
        '/paypal/capture-order/',
        '/paypal/create-order',
        '/config/paypal',
        '/paypal/payment-success'
    ];

    $current_route = Flight::request()->url;

    // Allow public routes to pass without JWT
    foreach ($public_routes as $route) {
        if (strpos($current_route, $route) === 0) {
            return true;
        }
    }

    try {
        // Normalize header keys to lowercase to avoid "Authorization" casing issues
        $headers = array_change_key_case(getallheaders(), CASE_LOWER);

        if (!isset($headers['authorization']) || !preg_match('/Bearer\s(\S+)/', $headers['authorization'], $matches)) {
            Flight::halt(401, json_encode(["error" => "Missing or malformed Authorization header"]));
        }

        $jwt = $matches[1];

        // Decode token
        $decoded = JWT::decode($jwt, new Key(JWT_SECRET, 'HS256'));

        // Save user to Flight globals
        Flight::set('user', (array)$decoded->user);
        Flight::set('jwt_token', $jwt);

        

        return true;
    } catch (\Exception $e) {
        Flight::halt(401, json_encode(["error" => "Invalid or expired token", "message" => $e->getMessage()]));
    }
});
