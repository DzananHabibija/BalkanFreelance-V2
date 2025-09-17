<?php
require_once __DIR__ . '/../vendor/autoload.php';

if (!class_exists(\Google\Client::class)) {
    die("Google Client class not found!");
}
use Google\Client;



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

    public static function getGoogleClient() {
        $client = new Client();
        $client->setClientId(GOOGLE_CLIENT_ID);
        $client->setClientSecret(GOOGLE_CLIENT_SECRET);
        $client->setRedirectUri(GOOGLE_REDIRECT_URI);
        $client->addScope('email');
        $client->addScope('profile');
        $client->setHttpClient(new \GuzzleHttp\Client([
            'verify' => false
        ]));

        return $client;
    }


    public static function validate_reserved_usernames($username) {
    $url = 'https://raw.githubusercontent.com/shouldbee/reserved-usernames/master/reserved-usernames.txt';
    
    $username = strtolower(trim($username));

    $reservedList = file_get_contents($url);

    if ($reservedList === false) {
        return [
            'reserved' => false,
            'message' => 'Could not fetch reserved usernames list.'
        ];
    }

    $reservedUsernames = array_map('trim', explode("\n", $reservedList));

    if (in_array($username, $reservedUsernames)) {
        return [
            'reserved' => true,
            'message' => 'Username is reserved and not allowed.'
        ];
    }

    return [
        'reserved' => false,
        'message' => 'Username is allowed.'
        ];
    }

    public static function validate_tlds($email){
        $tlds = file_get_contents('https://data.iana.org/TLD/tlds-alpha-by-domain.txt');
        $email_parts = explode('@', $email);
        $domain = array_pop($email_parts);
        $domain_parts = explode('.', $domain);
        $tld = strtoupper(array_pop($domain_parts));
        $tlds_array = explode("\n", $tlds);

        if (in_array($tld, $tlds_array)) {
            return true;
        } else {
            return false;
        }
    }

    

    public static function validate_mx_record($email){
    $parts = explode("@", $email);
    $domain = $parts[1];

    return getmxrr($domain, $mx_details) && !empty($mx_details);
    }


    
    public static function validate_phone_number($phone_number){
        $phone_util = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
        $number_proto = $phone_util->parse($phone_number, "BA");
        if ($phone_util->getNumberType($number_proto) === \libphonenumber\PhoneNumberType::MOBILE) {
            return true;
        } else {
            return false;


        }
        } catch (\libphonenumber\NumberParseException $e) {
        echo $e->getMessage();
        }

    }
}