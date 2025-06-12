<?php
use Google\Service\Oauth2;

require_once __DIR__ . '/../services/UserService.php';

// Ensure Google API Client autoload is included
require_once __DIR__ . '/../../vendor/autoload.php';


Flight::set("user_service",new UserService());

/**
 * @OA\Get(
 *     path="/users/{user_id}",
 *     summary="Get a user by ID",
 *     tags={"Users"},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="User data")
 * )
 */
Flight::route('GET /users/@user_id', function ($id) {
    $service = Flight::get("user_service");
    $user = $service->get_user_by_id($id);
    Flight::json($user);
});

/**
 * @OA\Post(
 *     path="/register",
 *     summary="Register a new user",
 *     tags={"Users"},
 *     @OA\Response(response=200, description="Registration process initiated")
 * )
 */
Flight::route('POST /register', function() {
    echo "Registering user...";
});

/**
 * @OA\Get(
 *     path="/users",
 *     summary="Get all users",
 *     tags={"Users"},
 *     @OA\Response(response=200, description="List of users")
 * )
 */
Flight::route('GET /users', function() {
    $service = Flight::get("user_service");
    $users = $service->get_users();
    Flight::json($users);
});

/**
 * @OA\Post(
 *     path="/users/add",
 *     summary="Add a new user",
 *     tags={"Users"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"first_name", "last_name", "email", "password"},
 *             @OA\Property(property="first_name", type="string"),
 *             @OA\Property(property="last_name", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="password", type="string"),
 *             @OA\Property(property="country_id", type="integer", nullable=true),
 *             @OA\Property(property="bio", type="string", nullable=true)
 *         )
 *     ),
 *     @OA\Response(response=200, description="User added successfully")
 * )
 */
Flight::route('POST /users/add', function() {

    $payload = Flight::request()->data->getData();
    
    //  if($payload['username'] == NULL || $payload['username'] == '') {
         
    //      Flight::halt(500,"Username field is missing");
    //  }
    
    
     $user = Flight::get('user_service')->add_user($payload);
    
   
    
    
    Flight::json($user);
    
    });

/**
 * @OA\Post(
 *     path="/otp/qr-code",
 *     summary="Generate a QR code for OTP",
 *     tags={"OTP"},
 *     @OA\Response(response=200, description="QR code generated")
 * )
 */
Flight::route('POST /otp/qr-code', function () {
    echo "Generating QR code...";
});

/**
 * @OA\Post(
 *     path="/otp/verify",
 *     summary="Verify OTP code",
 *     tags={"OTP"},
 *     @OA\Response(response=200, description="OTP verified")
 * )
 */
Flight::route('POST /otp/verify', function () {
    echo "Verifying OTP...";
});
    
/**
 * @OA\Delete(
 *     path="/users/delete/{user_id}",
 *     summary="Delete a user by ID",
 *     tags={"Users"},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="User deleted successfully")
 * )
 */
Flight::route('DELETE /users/delete/@user_id', function ($id) {
    $service = Flight::get("user_service");
    $user = $service->delete_user($id);
    Flight::json(["message" => "You have successfully delelted the user!"]);
});


/**
 * @OA\Get(
 *     path="/google-login",
 *     summary="Start Google OAuth login",
 *     tags={"OAuth"},
 *     @OA\Response(response=200, description="Returns Google auth URL")
 * )
 */
 Flight::route('GET /google-login', function () {
        $client = Utils::getGoogleClient();
        $authUrl = $client->createAuthUrl();

        if ($authUrl) {
            Flight::json(['authUrl' => $authUrl]);
        } else {
            Flight::json(['error' => 'Unable to create auth URL'], 500);
        }
    });


/**
 * @OA\Get(
 *     path="/google-callback",
 *     summary="Handle Google OAuth callback",
 *     tags={"OAuth"},
 *     @OA\Parameter(
 *         name="code",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(response=200, description="Handles Google login and redirects with JWT")
 * )
 */
Flight::route('GET /google-callback', function () {
    $client = Utils::getGoogleClient();

    if (!isset(Flight::request()->query['code'])) {
        Flight::json(['error' => 'Authorization code not provided'], 400);
        return;
    }

    $authCode = Flight::request()->query['code'];
    $token = $client->fetchAccessTokenWithAuthCode($authCode);

    if (isset($token['error'])) {
        Flight::json(['error' => $token['error_description']], 400);
        return;
    }

    $client->setAccessToken($token['access_token']);
    $oauth2 = new Oauth2($client);
    $googleUserInfo = $oauth2->userinfo->get();

    $email = $googleUserInfo->email;
    $firstName = $googleUserInfo->givenName;
    $lastName = $googleUserInfo->familyName;

    $userService = Flight::get('user_service');
    $user = $userService->get_user_by_email($email); // Adjust this method if needed

    if (!$user) {
        $randomPassword = bin2hex(random_bytes(12));
        $hashedPassword = Utils::hash_my_password($randomPassword);

        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password' => $hashedPassword,
            'country_id' => null,
            'profile_image' => null,
            'bio' => null,
            'balance' => 0.00,
            'isAdmin' => 0,
        ];

        $userDao = Flight::get("user_service")->get_user_dao();
        $user = $userDao->insert_google_user('users', $userData);
     // Create this method to match your insert logic
    }

    // Generate JWT
    $jwtPayload = [
        'id' => $user['id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'iat' => time(),
        'exp' => time() + 3600 // 1 hour expiry
    ];
    $jwt = \Firebase\JWT\JWT::encode($jwtPayload, JWT_SECRET, 'HS256');

    // Redirect with token
    $baseFrontendUrl = "http://localhost/balkanfreelance/frontend/#home";
    Flight::redirect($baseFrontendUrl . "?jwt=" . urlencode($jwt));
});


