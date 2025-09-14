<?php

require_once __DIR__ . '/../services/PaymentService.php';

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



