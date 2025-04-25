<?php

require_once __DIR__ . '/../dao/UserDao.php';

class UserService
{
    protected $dao;

    public function __construct()
    {
        $this->dao = new UserDao();
    }

    public function get_user_by_id($id) {
        return $this->dao->get_user_by_id($id);
    }

    public function add_user($user){
        return $this->dao->add_user($user);
    }

    public function get_user_by_email_or_username($value) {
        return $this->dao->get_user_by_email_or_username($value);
    }

    public function get_user_by_email_or_username_combined($email, $username) {
        return $this->dao->get_user_by_email_or_username_combined($email, $username);
    }
    
    public function get_user_by_email_or_username_single($value) {
        return $this->dao->get_user_by_email_or_username_single($value);
    }
    

    public function get_otp_secret_by_email($email) {
        return $this->dao->get_otp_secret_by_email($email);
    }

    public function get_users() {
        return $this->dao->get_users();
    }

    public function delete_user($id) {
        return $this->dao->delete_user($id);
    }
    
    
}