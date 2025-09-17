<?php
require_once __DIR__ . '/../dao/ReviewDao.php';

class ReviewService {
    private $dao;

    public function __construct() {
        $this->dao = new ReviewDao();
    }

    public function add_review($data) {
        // Basic validation
        if (!isset($data['reviewer_id'], $data['reviewed_id'], $data['gig_id'], $data['rating'])) {
            throw new Exception("Missing required fields.");
        }

        if ($data['reviewer_id'] == $data['reviewed_id']) {
            throw new Exception("You cannot review yourself.");
        }

        if ($data['rating'] < 1 || $data['rating'] > 5) {
            throw new Exception("Rating must be between 1 and 5.");
        }

        return $this->dao->insert_review($data);
    }

    public function get_user_rating_summary($user_id) {
        return $this->dao->fetch_rating_summary($user_id);
    }
}
