<?php

require __DIR__ . '/../../../vendor/autoload.php';

define('BASE_URL', 'http://localhost/BalkanFreelance/backend/');

error_reporting(0);

$openapi = \OpenApi\Generator::scan(['../../../app/routes', './']);
header('Content-Type: application/x-yaml');
echo $openapi->toYaml();
?>
