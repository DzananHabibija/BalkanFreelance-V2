<?php

require_once __DIR__ . '/../dao/AuthDao.php';

class AuthService {
    private $auth_dao;
    public function __construct() {
        $this->auth_dao = new AuthDao();
    }
    public function get_user_by_email($email){
        
        return $this->auth_dao->get_user_by_email($email);
    }

    
    // public function get_user_by_email_or_username($value) {
    //     return $this->auth_dao->get_user_by_email_or_username($value);
    // }

    public function add_user($user) {
        return $this->auth_dao->add_user($user);
    }

}