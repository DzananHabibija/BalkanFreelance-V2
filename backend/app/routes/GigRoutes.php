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
Flight::route('POST /gigs/add', function () {
    $service = Flight::get("gig_service");

    // Multipart form: access via $_POST and $_FILES
    $gig = $_POST;

    // Handle image upload
    if (!empty($_FILES['image']['tmp_name'])) {
        $uploadsDir = __DIR__ . '/../../../uploads/gig_images/';
        if (!file_exists($uploadsDir)) {
            mkdir($uploadsDir, 0777, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = uniqid('gig_') . "." . $ext;
        $destination = $uploadsDir . $filename;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
            $gig['gig_image_url'] = '/uploads/gig_images/' . $filename;
        } else {
            Flight::halt(500, "Failed to upload image");
        }
    }

    // Add timestamp if not sent
    if (!isset($gig['created_at']) || empty($gig['created_at'])) {
        $gig['created_at'] = date('Y-m-d H:i:s');
    }

    $createdGig = $service->add_gig($gig);
    Flight::json($createdGig);
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
Flight::route('GET /gigs/all', function() {
    $service = Flight::get("gig_service");
    $gigs = $service->get_gigs();
    Flight::json($gigs);
});


Flight::route('GET /gigs', function () {
    $minPrice      = isset($_GET['min_price']) ? floatval($_GET['min_price']) : null;
    $maxPrice      = isset($_GET['max_price']) ? floatval($_GET['max_price']) : null;
    $categoryId    = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
    $postedWithin  = isset($_GET['posted_within']) ? $_GET['posted_within'] : null; // 'week'|'month'|'year'
    $excludeUserId = isset($_GET['excludeUser']) ? intval($_GET['excludeUser']) : null;
    $q             = isset($_GET['q']) ? trim($_GET['q']) : null;

    $filters = [
        'min_price'     => $minPrice !== null && $minPrice !== '' ? $minPrice : null,
        'max_price'     => $maxPrice !== null && $maxPrice !== '' ? $maxPrice : null,
        'category_id'   => $categoryId !== null && $categoryId !== '' ? $categoryId : null,
        'posted_within' => in_array($postedWithin, ['week','month','year'], true) ? $postedWithin : null,
        'exclude_user'  => $excludeUserId !== null && $excludeUserId !== '' ? $excludeUserId : null,
        'q'             => $q !== null && $q !== '' ? $q : null,
    ];

    $gigService = new GigService();
    $gigs = $gigService->getAllWithFilters($filters);

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


Flight::route('PUT /gigs/@id', function($id) {
    $data = Flight::request()->data->getData();
    $title = $data['title'] ?? null;
    $price = $data['price'] ?? null;
    $status = $data['status'] ?? null;

    $gigService = new GigService();
    $updatedGig = $gigService->updateGig($id, $title, $price, $status);

    Flight::json($updatedGig);
});

Flight::route('GET /gigs/full/@id', function($id){
    $service = Flight::get("gig_service");
    Flight::json($service->getGigByIdWithUser($id));
});

Flight::route('POST /gigs/@id/apply', function ($gig_id) {
    $data = Flight::request()->data->getData();
    $user_id = $data['user_id'];
    $cover_letter = $data['cover_letter'] ?? '';

    $service = Flight::get('gig_service');
    $service->apply_to_gig($gig_id, $user_id, $cover_letter);

    Flight::json(['message' => 'Application submitted']);
});

Flight::route('GET /gigs/@id/applications', function ($gig_id) {
    $service = Flight::get("gig_service");
    $applications = $service->get_applications_for_gig($gig_id);
    Flight::json($applications);
});

Flight::route('POST /gigs/@gig_id/approve/@user_id', function ($gig_id, $user_id) {
    $service = Flight::get("gig_service");
    $service->approve_applicant($gig_id, $user_id);
    Flight::json(['message' => 'Applicant approved']);
});

Flight::route('GET /gigs/@gig_id/application-status/@user_id', function ($gig_id, $user_id) {
    $service = Flight::get("gig_service");
    $status = $service->get_application_status($gig_id, $user_id);
    Flight::json($status);
});

Flight::route('POST /gigs/@gig_id/pay/@freelancer_id', function ($gig_id, $freelancer_id) {
    $data = Flight::request()->data->getData();
    $payer_id = $data['payer_id']; // ID of the gig owner
    $service = Flight::get("gig_service");

    $result = $service->pay_freelancer($gig_id, $payer_id, $freelancer_id);

    if ($result['success']) {
        Flight::json(['message' => 'Payment successful']);
    } else {
        Flight::halt(400, $result['error']);
    }
});
