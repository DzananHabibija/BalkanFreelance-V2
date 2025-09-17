<?php

require_once __DIR__ . '/../services/PaymentService.php';
require_once __DIR__ . '/../../config/config.php';



Flight::set('payment_service', new PaymentService());

/**
 * @OA\Post(
 *     path="/paypal/payment-success",
 *     summary="Handle PayPal payment success callback",
 *     tags={"Payments"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"sender_id", "receiver_id", "gig_id", "amount"},
 *             @OA\Property(property="sender_id", type="integer"),
 *             @OA\Property(property="receiver_id", type="integer"),
 *             @OA\Property(property="gig_id", type="integer"),
 *             @OA\Property(property="amount", type="number")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Payment recorded successfully")
 * )
 */
Flight::route('POST /paypal/payment-success', function () {
    $data = Flight::request()->data->getData();

    if (!isset($data['sender_id'], $data['receiver_id'], $data['gig_id'], $data['amount'])) {
        Flight::json(['error' => 'Missing required payment fields'], 400);
        return;
    }

    Flight::get('payment_service')->handlePayPalPayment($data);

    Flight::json(['success' => true, 'message' => 'PayPal payment processed']);
});


Flight::route('GET /config/paypal', function () {
    Flight::json([
        "clientId" => PAYPAL_CLIENT_ID
    ]);
});


Flight::route('POST /paypal/create-order', function () {
    $data = Flight::request()->data->getData();

    // 1. Uzmemo access token
    $ch = curl_init("https://api-m.sandbox.paypal.com/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json", "Accept-Language: en_US"]);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $auth = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $accessToken = $auth['access_token'];

    // 2. Kreiramo order
    $ch = curl_init("https://api-m.sandbox.paypal.com/v2/checkout/orders");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "intent" => "CAPTURE",
        "purchase_units" => [[
            "amount" => [
                "currency_code" => "USD",
                "value" => $data['amount']
            ]
        ]]
    ]));
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);
 
    error_log("PayPal order response: " . print_r($response, true));
    Flight::json(["id" => $response["id"]]);
});


Flight::route('POST /paypal/capture-order/@id', function($id) {
    // 1. access token
    $ch = curl_init("https://api-m.sandbox.paypal.com/v1/oauth2/token");
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Accept: application/json", "Accept-Language: en_US"]);
    curl_setopt($ch, CURLOPT_USERPWD, PAYPAL_CLIENT_ID . ":" . PAYPAL_CLIENT_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $auth = json_decode(curl_exec($ch), true);
    curl_close($ch);

    $accessToken = $auth['access_token'];

    // 2. capture order
    $ch = curl_init("https://api-m.sandbox.paypal.com/v2/checkout/orders/$id/capture");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $accessToken
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "{}"); // empty JSON body
    $response = json_decode(curl_exec($ch), true);
    curl_close($ch);

    Flight::json($response);
});
