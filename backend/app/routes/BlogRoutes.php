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

Flight::route('POST /blogs/add', function () {
    $blogService = Flight::get("blog_service");

    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $admin_id = $_POST['admin_id'] ?? '';
    $published_at = $_POST['published_at'] ?? null;

    $image_url = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // ✅ Save to root-level uploads/blogs
        $uploadDir = dirname(__DIR__, 3) . '/uploads/blogs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid("blog_", true) . '.' . $ext;
        $uploadPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $image_url = 'blogs/' . $uniqueName;
        }
    }

    $blogData = [
        'admin_id' => $admin_id,
        'title' => $title,
        'content' => $content,
        'image_url' => $image_url,
        'published_at' => $published_at
    ];

    $blog = $blogService->add_blog($blogData);
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
    $success = Flight::get("blog_service")->delete_blog($id);
    if ($success) {
        Flight::json(["message" => "You have successfully deleted the blog!"]);
    } else {
        Flight::halt(404, "Blog not found.");
    }
});



Flight::route('POST /blogs/update', function () {
    $service = Flight::get("blog_service");

    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';
    $published_at = $_POST['published_at'] ?? null;

    $image_url = null;

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = dirname(__DIR__, 3) . '/uploads/blogs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $uniqueName = uniqid("blog_", true) . '.' . $ext;
        $uploadPath = $uploadDir . $uniqueName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $image_url = 'blogs/' . $uniqueName;
        }
    } else {
        // Keep current image if no new one uploaded
        $existing = $service->get_blog_by_id($id);
        $image_url = $existing['image_url'] ?? null;
    }

    $updated = $service->update_blog([
        'id' => $id,
        'title' => $title,
        'content' => $content,
        'image_url' => $image_url,
        'published_at' => $published_at
    ]);

    Flight::json(["message" => "Blog updated", "blog" => $updated]);
});




