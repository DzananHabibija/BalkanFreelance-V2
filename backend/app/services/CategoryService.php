<?php

require_once __DIR__ . '/../dao/CategoryDao.php';

class CategoryService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new CategoryDao();
    }

    public function get_categories()
    {
        return $this->dao->get_categories();
    }

    public function get_category_by_id($id)
    {
        return $this->dao->get_category_by_id($id);
    }

    public function add_category($category)
    {
        return $this->dao->add_category($category);
    }

    public function delete_category($id)
    {
        return $this->dao->delete_category($id);
    }

    public function update_category($data) {
    return $this->dao->update_category($data);
}
}
