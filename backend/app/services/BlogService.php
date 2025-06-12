<?php

require_once __DIR__ . '/../dao/BlogDao.php';

Class BlogService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new BlogDao();
    }

    public function get_blog_by_id($id) {
        return $this->dao->get_blog_by_id($id);
    }

    public function add_blog($blog){
         if (!isset($blog['published_at']) || empty($blog['published_at'])) {
             $blog['published_at'] = date('Y-m-d H:i:s');
         }
        return $this->dao->add_blog($blog);
    }

    public function get_blogs(){
        return $this->dao->get_blogs();
    }

    public function delete_blog($id){
        return $this->dao->delete_blog($id);
    }

}