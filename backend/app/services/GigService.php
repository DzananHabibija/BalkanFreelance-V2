<?php

require_once __DIR__ . '/../dao/GigDao.php';

Class GigService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new GigDao();
    }

    public function get_gig_by_id($id) {
        return $this->dao->get_gig_by_id($id);
    }

    public function add_gig($gig){
        if (!isset($gig['created_at']) || empty($gig['created_at'])) {
            $gig['created_at'] = date('Y-m-d H:i:s');
        }
        return $this->dao->add_gig($gig);
    }

    public function get_gigs(){
        return $this->dao->get_gigs();
    }

    public function update_gig($id, $title, $description, $price, $status){
        return $this->dao->update_gig($id, $title, $description, $price, $status);
    }

    public function delete_gig($id){
        return $this->dao->delete_gig($id);
    }

}