<?php
function get_base_urls() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\'); // e.g. /sssd/front

    $baseRoot = preg_replace('/\/frontend$/', '', $path);
    $baseUrl = $protocol . $host . $baseRoot ;
    $frontUrl = $protocol . $host . $baseRoot . '/frontend/';

    return [
        'base_url' => $baseUrl,
        'front_url' => $frontUrl
    ];
}
$baseUrl = get_base_urls()['base_url'];
$frontUrl = get_base_urls()['front_url'];
header("Location: $frontUrl");
      exit;