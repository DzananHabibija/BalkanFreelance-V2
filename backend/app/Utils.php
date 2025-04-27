<?php
class Utils{
    public static function hash_my_password($password){

        $hash = password_hash($password, PASSWORD_DEFAULT);
        return $hash;

    }

    public static function verify_my_password($userPassword, $hashed_password) {
        return password_verify($userPassword, $hashed_password);
    }

    public static function check_if_password_breached($password){
    
    $sha1Password = strtoupper(sha1($password));
    #echo $sha1Password;

    $prefix = substr($sha1Password, 0, 5); #4BC93
    $suffix = substr($sha1Password, 5);

    $check_password = file_get_contents('https://api.pwnedpasswords.com/range/'.$prefix);

    if (str_contains($check_password,$suffix)){
        return [
            'breached' => true,
            'message' => 'Password has been previously breached!'
        ];
    }
    else {
        return [
            'breached' => false,
            'message' => 'Congrats, this is a safe password!'
        ];
    }
    }
}