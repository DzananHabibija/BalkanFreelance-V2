<?php
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../Utils.php';
require_once __DIR__ . '/../../config/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

Flight::set('auth_service', new AuthService());

/**
 * @OA\Post(
 *     path="/auth/login",
 *     summary="Log in a user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"email", "password"},
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="password", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="User logged in with JWT")
 * )
 */
Flight::route('POST /auth/login', function() {
    foreach ($_COOKIE as $cookie_name => $cookie_value) {
        setcookie($cookie_name, '', time() - 3600, '/');
        unset($_COOKIE[$cookie_name]);
    }
    $payload = Flight::request()->data->getData();

    if (empty($payload['email']) || empty($payload['password'])) {
        Flight::halt(400, "Email and password are required");
    }

    $user = Flight::get('auth_service')->get_user_by_email($payload['email']);

     if (!$user || !Utils::verify_my_password($payload['password'], $user['password'])) {
         Flight::halt(401, "Invalid email or password");
     }

    $user_id = $user['id'];
    $user_email = $user['email'];
    unset($user['password']); 
    $jwt_payload = [
        'user' => [
            'id' => $user_id,
            'email' => $user_email
        ],
        'iat' => time(),
        'exp' => time() + (60 * 60 * 24) // 1 day valid
    ];

    $token = JWT::encode(
        $jwt_payload,
        JWT_SECRET,
        'HS256'
    );


    // Flight::json([
    //     'message' => 'Login successful',
    //     'user' => $user,
    //     'token' => $token
    // ]);

    Flight::json(
        array_merge($user, ['token' => $token])
    );

});


/**
 * @OA\Post(
 *     path="/auth/register",
 *     summary="Register a new user",
 *     tags={"Authentication"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"first_name", "last_name", "email", "password", "country_id"},
 *             @OA\Property(property="first_name", type="string"),
 *             @OA\Property(property="last_name", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="password", type="string"),
 *             @OA\Property(property="country_id", type="integer"),
 *             @OA\Property(property="phone_number", type="string", example="+38761111222")
 *         )
 *     ),
 *     @OA\Response(response=200, description="User registered")
 * )
 */
Flight::route('POST /auth/register', function() {
    $data = Flight::request()->data->getData();

    $required_fields = ['first_name','last_name', 'email', 'password', 'country_id', 'phone_number'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            Flight::halt(400, "Missing field: $field");
        }
    }

    // if (!preg_match('/^\+?[0-9\s\-()]{6,20}$/', $data['phone_number'])) {
    //     Flight::halt(400, "Invalid phone number format.");
    // }   


    if (mb_strlen($data['first_name']) <= 1) {
        Flight::halt(400, "Please provide a longer first name.");
    }

    if (mb_strlen($data['last_name']) <= 1) {
        Flight::halt(400, "Please provide a longer last name.");
    }

    if (!ctype_alpha($data['first_name'])) {
        Flight::halt(400, "First name can only contain letters.");
    }

    if (!ctype_alpha($data['last_name'])) {
        Flight::halt(400, "Last name can only contain letters.");
    }

    if (mb_strlen($data['password']) < 8) {
        Flight::halt(400, "Password must have at least 8 characters.");
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        Flight::halt(400, "Invalid email format.");
    }

    if (Flight::get('auth_service')->get_user_by_email($data['email']) ) { #||Flight::get('auth_service')->get_user_by_email_or_username($data['username'])
        Flight::halt(400, "This email is already associated with an existing account"); #or "Username already in use"
    }   

        
    if (!Utils::validate_tlds($data['email'])) {
        Flight::halt(400,'Invalid top-level domain (TLD) in email!');
        return;
    }

     
    if (!Utils::validate_mx_record($data['email'])) {
        Flight::halt(400,'No MX records found for the email domain!');
        return;
    }


    $breached = Utils::check_if_password_breached($data['password']);
    if ($breached['breached']) {
        Flight::halt(400, $breached['message']);
    }

    if (!Utils::validate_phone_number($data['phone_number'])) {
        Flight::halt(400,'Invalid phone number format!');
        return;
    }



    // if (Utils::check_if_password_breached($data['password'])) {
    //     Flight::halt(400, "This password has been breached before. Please choose a stronger password.");
    // }

   

    $data['password'] = Utils::hash_my_password($data['password']);

    $newUser = Flight::get('auth_service')->add_user($data);

    unset($newUser['password']); 

    Flight::json([
        'message' => 'Registration successful',
        'user' => $newUser
    ]);
});

/**
 * @OA\Post(
 *     path="/auth/logout",
 *     summary="Logout and decode JWT",
 *     tags={"Authentication"},
 *     @OA\Response(response=200, description="JWT decoded and user info returned"),
 *     @OA\Response(response=401, description="Unauthorized")
 * )
 */
Flight::route('POST /auth/logout', function(){
    foreach ($_COOKIE as $cookie_name => $cookie_value) {
        setcookie($cookie_name, '', time() - 3600, '/');
        unset($_COOKIE[$cookie_name]);
    }
    // try {
    //     $token = Flight::request()->getHeader("Authentication");
    //     if(!$token)
    //         Flight::halt(401, "Missing authentication header");

    //     $decoded_token = JWT::decode($token, new Key(JWT_SECRET, 'HS256'));

    //     Flight::json([
    //         'jwt_decoded' => $decoded_token,
    //         'user' => $decoded_token->user
    //     ]);
    // } catch (\Exception $e) {
    //     Flight::halt(401, $e->getMessage());
    // }

});
