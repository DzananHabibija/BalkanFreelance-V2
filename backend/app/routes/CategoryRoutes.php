<?php

require_once __DIR__ . '/../services/CategoryService.php';

Flight::set("category_service", new CategoryService());

/**
 * @OA\Get(
 *     path="/categories/{category_id}",
 *     summary="Get category by ID",
 *     tags={"Categories"},
 *     @OA\Parameter(
 *         name="category_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Category data")
 * )
 */
Flight::route('GET /categories/@category_id', function ($id) {
    $service = Flight::get("category_service");
    $category = $service->get_category_by_id($id);
    Flight::json($category);
});

/**
 * @OA\Post(
 *     path="/categories/add",
 *     summary="Add a new category",
 *     tags={"Categories"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"name"},
 *             @OA\Property(property="name", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Category added")
 * )
 */
Flight::route('POST /categories/add', function() {
    $payload = Flight::request()->data->getData();
    $category = Flight::get('category_service')->add_category($payload);
    Flight::json($category);
});

/**
 * @OA\Get(
 *     path="/categories",
 *     summary="Get all categories",
 *     tags={"Categories"},
 *     @OA\Response(response=200, description="List of categories")
 * )
 */
Flight::route('GET /categories', function() {
    $service = Flight::get("category_service");
    $categories = $service->get_categories();
    Flight::json($categories);
});

/**
 * @OA\Delete(
 *     path="/categories/delete/{category_id}",
 *     summary="Delete category by ID",
 *     tags={"Categories"},
 *     @OA\Parameter(
 *         name="category_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Category deleted")
 * )
 */
Flight::route('DELETE /categories/delete/@category_id', function ($id) {
    $success = Flight::get("category_service")->delete_category($id);
    if ($success) {
        Flight::json(["message" => "You have successfully deleted the category!"]);
    } else {
        Flight::halt(404, "Category not found.");
    }
});



/**
 * @OA\Post(
 *     path="/categories/update",
 *     summary="Update a category",
 *     tags={"Categories"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"id", "name"},
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="name", type="string")
 *         )
 *     ),
 *     @OA\Response(response=200, description="Category updated")
 * )
 */
Flight::route('POST /categories/update', function () {
    $payload = Flight::request()->data->getData();
    $updated = Flight::get("category_service")->update_category($payload);
    Flight::json(["message" => "Category updated", "category" => $updated]);
});
