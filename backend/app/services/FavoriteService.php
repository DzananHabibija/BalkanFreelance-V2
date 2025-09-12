<?php

require_once __DIR__ . '/../dao/FavoriteDao.php';

Class FavoriteService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new FavoriteDao();
    }

    public function add_favorite($user_id, $gig_id) {
        return $this->dao->add_favorite($user_id, $gig_id);
    }

    public function remove_favorite($user_id, $gig_id) {
        return $this->dao->remove_favorite($user_id, $gig_id);
    }

    public function get_favorites($user_id) {
        return $this->dao->get_favorites($user_id);
    }

    public function is_favorite($user_id, $gig_id) {
        return $this->dao->is_favorite($user_id, $gig_id);
    }
}