<?php

require_once __DIR__ . '/../services/FavoriteService.php';

Flight::set("favorite_service", new FavoriteService());

/**
 * @OA\Get(
 *     path="/favorites/{user_id}",
 *     summary="Get user's favorite gigs",
 *     tags={"Favorites"},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns user's favorite gigs",
 *         @OA\JsonContent(
 *             @OA\Property(property="gig_id", type="integer"),
 *         )
 *     )
 * )
 */
Flight::route('GET /favorites/@user_id', function ($id) {
    $service = Flight::get("favorite_service");
    $favorites = $service->get_favorites($id);
    Flight::json($favorites);
});


Flight::route('POST /favorites/add', function () {

    $service = Flight::get("favorite_service");

    $user_id = $_POST['user_id'];
    $gig_id = $_POST['gig_id'];

    $service->add_favorite($user_id, $gig_id);

    Flight::json(["message" => "Added to favorites!"]);
});

/**
 * @OA\Delete(
 *     path="/favorites/{user_id}/{gig_id}",
 *     summary="Remove a favorite gig",
 *     tags={"Favorites"},
 *     @OA\Parameter(
 *         name="user_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="gig_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Favorite removed"
 *     )
 * )
 */
Flight::route('DELETE /favorites/delete/@user_id/@gig_id', function ($user_id, $gig_id) {
    $service = Flight::get("favorite_service");

    $success = $service->remove_favorite($user_id, $gig_id);

    if ($success) {
        Flight::json(["message" => "Removed from favorites!"]);
    } else {
        Flight::json(["error" => "Failed to remove from favorites!"]);
    }
});


Flight::route('GET /favorites/@user_id/@gig_id', function ($user_id, $gig_id) {
    $service = Flight::get("favorite_service");

    $response = $service->is_favorite($user_id, $gig_id);
    Flight::json(["is_favorite" => sizeof($response) !== 0]);
});