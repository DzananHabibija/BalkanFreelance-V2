<?php
require_once __DIR__ . '/../services/ReviewService.php';

Flight::set('review_service', new ReviewService());

/**
 * @OA\Post(
 *     path="/reviews",
 *     summary="Leave a review after a successful payment",
 *     tags={"Reviews"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"reviewer_id", "reviewed_id", "gig_id", "rating"},
 *             @OA\Property(property="reviewer_id", type="integer"),
 *             @OA\Property(property="reviewed_id", type="integer"),
 *             @OA\Property(property="gig_id", type="integer"),
 *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
 *             @OA\Property(property="review_comment", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Review successfully submitted"),
 *     @OA\Response(response=400, description="Validation or insertion error")
 * )
 */
Flight::route('POST /reviews', function() {
    $data = Flight::request()->data->getData();

    try {
        $review = Flight::get('review_service')->add_review($data);
        Flight::json(["message" => "Review submitted successfully", "data" => $review]);
    } catch (Exception $e) {
        Flight::json(["error" => $e->getMessage()], 400);
    }
});

/**
 * @OA\Get(
 *     path="/reviews/summary/{user_id}",
 *     summary="Get average rating and total reviews for a user",
 *     tags={"Reviews"},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Rating summary")
 * )
 */
Flight::route('GET /reviews/summary/@user_id', function($user_id) {
    $summary = Flight::get('review_service')->get_user_rating_summary($user_id);
    Flight::json($summary);
});
