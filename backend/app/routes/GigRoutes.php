<?php

require_once __DIR__ . '/../services/GigService.php';

Flight::set("gig_service", new GigService());

/**
 * @OA\Get(
 *     path="/gigs/{gig_id}",
 *     summary="Get a gig by ID",
 *     tags={"Gigs"},
 *     @OA\Parameter(
 *         name="gig_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Gig data"
 *     )
 * )
 */
Flight::route('GET /gigs/@gig_id', function ($id) {
    $service = Flight::get("gig_service");
    $gig = $service->get_gig_by_id($id);
    Flight::json($gig);
});

/**
 * @OA\Post(
 *     path="/gigs/add",
 *     summary="Add a new gig",
 *     tags={"Gigs"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "description", "price"},
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="price", type="number")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Gig created successfully"
 *     )
 * )
 */
Flight::route('POST /gigs/add', function() {
    $payload = Flight::request()->data->getData();
    $gig = Flight::get('gig_service')->add_gig($payload);
    Flight::json($gig);
});

/**
 * @OA\Get(
 *     path="/gigs",
 *     summary="Get all gigs",
 *     tags={"Gigs"},
 *     @OA\Response(
 *         response=200,
 *         description="List of gigs"
 *     )
 * )
 */
Flight::route('GET /gigs', function() {
    $service = Flight::get("gig_service");
    $gigs = $service->get_gigs();
    Flight::json($gigs);
});

/**
 * @OA\Delete(
 *     path="/gigs/delete/{gig_id}",
 *     summary="Delete a gig by ID",
 *     tags={"Gigs"},
 *     @OA\Parameter(
 *         name="gig_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Gig deleted successfully"
 *     )
 * )
 */
Flight::route('DELETE /gigs/delete/@gig_id', function ($id) {
    $service = Flight::get("gig_service");
    $success = $service->delete_gig($id);

    if ($success) {
        Flight::json(["message" => "You have successfully deleted the gig!"]);
    } else {
        Flight::halt(404, "Gig not found.");
    }
});

/**
 * @OA\Get(
 *     path="/gigs/search/{term}",
 *     summary="Search gigs by term",
 *     tags={"Gigs"},
 *     @OA\Parameter(
 *         name="term",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Search results"
 *     )
 * )
 */
Flight::route('GET /gigs/search/@term', function ($term) {
    $service = Flight::get("gig_service");
    $gigs = $service->search_gigs($term);
    Flight::json($gigs);
});

/**
 * @OA\Post(
 *     path="/gigs/update",
 *     summary="Update a gig",
 *     tags={"Gigs"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id", "title", "description", "price"},
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="description", type="string"),
 *             @OA\Property(property="tags", type="string"),
 *             @OA\Property(property="price", type="number"),
 *             @OA\Property(property="status", type="string", nullable=true)
 *         )
 *     ),
 *     @OA\Response(response=200, description="Gig updated successfully")
 * )
 */
Flight::route('POST /gigs/update', function () {
    $data = Flight::request()->data->getData();
    $service = Flight::get("gig_service");
    $updated = $service->update_gig($data);
    Flight::json(["message" => "Gig updated", "gig" => $updated]);
});
