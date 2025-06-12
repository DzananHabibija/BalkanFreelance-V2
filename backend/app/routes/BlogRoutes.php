<?php

require_once __DIR__ . '/../services/BlogService.php';

Flight::set("blog_service", new BlogService());

/**
 * @OA\Get(
 *     path="/blogs/{blog_id}",
 *     summary="Get a blog post by ID",
 *     tags={"Blogs"},
 *     @OA\Parameter(
 *         name="blog_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Returns a blog post",
 *         @OA\JsonContent(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="admin_id", type="integer"),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="content", type="string"),
 *             @OA\Property(property="image_url", type="string"),
 *             @OA\Property(property="published_at", type="string", format="date-time")
 *         )
 *     )
 * )
 */
Flight::route('GET /blogs/@blog_id', function ($id) {
    $service = Flight::get("blog_service");
    $blog = $service->get_blog_by_id($id);
    Flight::json($blog);
});

/**
 * @OA\Post(
 *     path="/blogs/add",
 *     summary="Add a new blog post",
 *     tags={"Blogs"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"admin_id", "title", "content"},
 *             @OA\Property(property="admin_id", type="integer"),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="content", type="string"),
 *             @OA\Property(property="image_url", type="string", nullable=true),
 *             @OA\Property(property="published_at", type="string", format="date-time", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Blog post created"
 *     )
 * )
 */
Flight::route('POST /blogs/add', function() {
    $payload = Flight::request()->data->getData();
    $blog = Flight::get('blog_service')->add_blog($payload);
    Flight::json($blog);
});

/**
 * @OA\Get(
 *     path="/blogs",
 *     summary="Get all blog posts",
 *     tags={"Blogs"},
 *     @OA\Response(
 *         response=200,
 *         description="List of all blog posts",
 *         @OA\JsonContent(type="array", @OA\Items(
 *             @OA\Property(property="id", type="integer"),
 *             @OA\Property(property="admin_id", type="integer"),
 *             @OA\Property(property="title", type="string"),
 *             @OA\Property(property="content", type="string"),
 *             @OA\Property(property="image_url", type="string"),
 *             @OA\Property(property="published_at", type="string", format="date-time")
 *         ))
 *     )
 * )
 */
Flight::route('GET /blogs', function() {
    $service = Flight::get("blog_service");
    $blogs = $service->get_blogs();
    Flight::json($blogs);
});

/**
 * @OA\Delete(
 *     path="/blogs/delete/{blog_id}",
 *     summary="Delete a blog post by ID",
 *     tags={"Blogs"},
 *     @OA\Parameter(
 *         name="blog_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Blog post deleted"
 *     )
 * )
 */
Flight::route('DELETE /blogs/delete/@blog_id', function ($id) {
    $service = Flight::get("blog_service");
    $blog = $service->delete_blog($id);
    Flight::json(["message" => "You have successfully deleted the blog!"]);
});

