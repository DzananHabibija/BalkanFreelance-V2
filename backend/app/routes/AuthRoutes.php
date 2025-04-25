<?php

require_once __DIR__ . '/../services/AuthService.class.php';



Flight::set('auth_service', new AuthService());



    Flight::route('POST /auth/login', function() {

        //$payload = $_REQUEST;
        $payload = Flight::request()->data->getData();    
        
        
         $user = Flight::get('auth_service')->get_user_by_email($payload['email']);
        //print_r("User is: ",$user); 
        //   print_r( $payload['password'] ."\n");
        //   print_r( $user['password']);
          //die;
       
         if(!$user || !password_verify($payload['password'], $user['password']))

            Flight::halt(401, "Invalid email or password, and user is: ",$user);
            
        
        unset($user['password']);

        $jwt_payload = [
            'user' => $user,
            'iat' => time(),
            // If this parameter is not set, JWT will be valid for life. This is not a good approach
            'exp' => time() + (60 * 60 * 24) // valid for day
        ];

        

        Flight::json(
            array_merge($user)
        );

        
        });



Flight::route('POST /auth/register', function() {
    $data = Flight::request()->data;

    // Validation: fullName, username, email, password, phoneNumber
    // Perform same checks as in Controller (lengths, format, regex, etc.)
    
    // Check if user already exists
    $existingUser = Flight::get('auth_service')->get_user_by_email_or_username_combined($data->email, $data->username);
    if ($existingUser) {
        Flight::halt(400, "Email or username already in use");
    }

    // Optional: password breach check (like you did with Utils::check_if_password_breached)
    
    // Hash password
    $data->password = password_hash($data->password, PASSWORD_DEFAULT);

    // Optional: generate OTP secret if you want to keep MFA

    // Save to DB
    $newUser = Flight::get('auth_service')->add_user($data);

    // Return response
    Flight::json([
        'message' => 'Registration successful',
        'user' => [
            'fullName' => $newUser['fullName'],
            'email' => $newUser['email'],
            'username' => $newUser['username']
        ]
    ]);
});
