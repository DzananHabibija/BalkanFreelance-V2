<?php
require_once __DIR__ . '/../../config/config.php';

class ReviewDao {
    private $conn;

    public function __construct() {
        $this->conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT,
            DB_USER,
            DB_PASSWORD
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function insert_review($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO reviews (reviewer_id, reviewed_id, gig_id, rating, review_comment)
            VALUES (:reviewer_id, :reviewed_id, :gig_id, :rating, :review_comment)
        ");

        $stmt->execute([
            ':reviewer_id' => $data['reviewer_id'],
            ':reviewed_id' => $data['reviewed_id'],
            ':gig_id' => $data['gig_id'],
            ':rating' => $data['rating'],
            ':review_comment' => $data['review_comment'] ?? null
        ]);

        return ["id" => $this->conn->lastInsertId()] + $data;
    }

    public function fetch_rating_summary($user_id) {
        $stmt = $this->conn->prepare("
            SELECT 
                ROUND(AVG(rating), 2) AS average_rating,
                COUNT(*) AS total_reviews
            FROM reviews
            WHERE reviewed_id = :user_id
        ");

        $stmt->execute([':user_id' => $user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
