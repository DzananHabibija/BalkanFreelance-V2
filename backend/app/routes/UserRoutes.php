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
    $data = Flight::request()->data->getData();

    $required_fields = ['first_name','last_name', 'email', 'password', 'country_id', 'phone_number'];
    foreach ($required_fields as $field) {
        if (empty($data[$field])) {
            Flight::halt(400, "Missing field: $field");
        }
    }

    if (!preg_match('/^\+?[0-9\s\-()]{6,20}$/', $data['phone_number'])) {
        Flight::halt(400, "Invalid phone number format.");
    }

    if (mb_strlen($data['first_name']) <= 1 || !ctype_alpha($data['first_name'])) {
        Flight::halt(400, "First name must be longer and contain only letters.");
    }

    if (mb_strlen($data['last_name']) <= 1 || !ctype_alpha($data['last_name'])) {
        Flight::halt(400, "Last name must be longer and contain only letters.");
    }

    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        Flight::halt(400, "Invalid email format.");
    }

    if (Flight::get('user_service')->get_user_by_email($data['email'])) {
        Flight::halt(400, "This email is already associated with an existing account.");
    }

    if (mb_strlen($data['password']) < 8) {
        Flight::halt(400, "Password must have at least 8 characters.");
    }

    $breached = Utils::check_if_password_breached($data['password']);
    if ($breached['breached']) {
        Flight::halt(400, $breached['message']);
    }

    $data['password'] = Utils::hash_my_password($data['password']);

    // Default values for admin panel users
    $data['bio'] = $data['bio'] ?? null;
    $data['balance'] = $data['balance'] ?? 0.00;
    $data['isAdmin'] = $data['isAdmin'] ?? 0;
    $data['profile_image'] = $data['profile_image'] ?? null;

    $newUser = Flight::get('user_service')->add_user($data);

    unset($newUser['password']);

    Flight::json([
        'message' => 'User created successfully via admin panel.',
        'user' => $newUser
    ]);
});



/**
 * @OA\Post(
 *     path="/users/update",
 *     summary="Update user by ID",
 *     tags={"Users"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id", "fullName", "email"},
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="fullName", type="string"),
 *             @OA\Property(property="email", type="string"),
 *             @OA\Property(property="countryId", type="integer"),
 *             @OA\Property(property="bio", type="string"),
 *             @OA\Property(property="balance", type="number"),
 *             @OA\Property(property="role", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="User updated successfully")
 * )
 */
Flight::route('POST /users/update', function () {
    $data = Flight::request()->data->getData();
    $service = Flight::get("user_service");
    $updated = $service->update_user($data);
    Flight::json(["message" => "User updated", "user" => $updated]);
});


Flight::route('GET /user-profile/@id', function($id){
    $userService = Flight::get("user_service");  // Use singleton
    $gigService = Flight::get("gig_service");

    $user = $userService->getUserById($id);
    $gigs = $userService->getUserGigs($id);

    foreach ($gigs as &$gig) {
        $gig['is_locked'] = $gigService->is_gig_locked($gig['id']);
    }

    if (!$user) {
        Flight::json(["error" => "User not found"], 404);
    } else {
        Flight::json([
            "user" => $user,
            "gigs" => $gigs
        ]);
    }
});



Flight::route('PUT /users/@id/bio', function($id) {
    $data = Flight::request()->data->getData();
    $bio = $data['bio'] ?? '';

    $userService = new UserService();
    $userService->updateBio($id, $bio);

    Flight::json(["message" => "Bio updated"]);
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
    foreach ($_COOKIE as $cookie_name => $cookie_value) {
        setcookie($cookie_name, '', time() - 3600, '/');
        unset($_COOKIE[$cookie_name]);
    }

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
    $lastName = $googleUserInfo->familyName ?? 'User';

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
            'is_admin' => 0,
        ];

        $userDao = Flight::get("user_service")->get_user_dao();
        $user = $userDao->insert_google_user('users', $userData);
     // Create this method to match your insert logic
    }

    // Generate JWT
    $jwtPayload = [
    'user' => [
        'id' => $user['id'],
        'email' => $user['email'],
        'first_name' => $user['first_name'],
        'last_name' => $user['last_name'],
        'is_admin' => $user['isAdmin']
        ],
        'iat' => time(),
        'exp' => time() + 3600
    ];

    $jwt = \Firebase\JWT\JWT::encode($jwtPayload, JWT_SECRET, 'HS256');

    setcookie("id", $jwtPayload["user"]["id"], time() + 3600, "/");
    setcookie("email", $jwtPayload["user"]["email"], time() + 3600, "/");
    setcookie("first_name", $jwtPayload["user"]["first_name"], time() + 3600, "/");
    setcookie("last_name", $jwtPayload["user"]["last_name"], time() + 3600, "/");
    setcookie("isAdmin", $jwtPayload["user"]["is_admin"], time() + 3600, "/");
    setcookie("jwt", $jwt, time() + 3600, "/");

    // Redirect with token
    $baseFrontendUrl = "http://localhost/BalkanFreelance/frontend/#home";
    Flight::redirect($baseFrontendUrl);
});


Flight::route('GET /user/@id/balance', function($id){
    $userService = Flight::get("user_service");
    $balance = $userService->getBalance($id);

    if ($balance === false) {
        Flight::json(["error" => "User not found or DB error"], 404);
    } else {
        Flight::json(["balance" => floatval($balance)]);
    }
});


Flight::route('POST /top-up', function () {
    $data = Flight::request()->data->getData();
    $amount = floatval($data['amount'] ?? 0);

    if ($amount <= 0) {
        Flight::json(["error" => "Invalid top-up amount."], 400);
        return;
    }

    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;

    if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        Flight::json(["error" => "Missing or invalid Authorization header"], 401);
        return;
    }

    $jwt = $matches[1];
    try {
        $decoded = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key(JWT_SECRET, 'HS256'));
        $userId = $decoded->user->id;

        $userService = Flight::get("user_service");
        $userService->increaseBalance($userId, $amount);

        Flight::json(["message" => "Balance updated successfully"]);
    } catch (Exception $e) {
        Flight::json(["error" => "Invalid or expired token: " . $e->getMessage()], 401);
    }
});

Flight::route('PUT /users/@id/phone', function($id) {
    $data = Flight::request()->data->getData();
    $service = Flight::get('user_service');
    $service->update_phone($id, $data['phone_number']);
    Flight::json(['message' => 'Phone updated']);
});