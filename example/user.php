<?php
header("Content-Type: application/json");
include __DIR__ . "/../vendor/autoload.php";
$api = new \Sovit\Instagram\Api();
$result = $api->getUser("natgeo");
echo json_encode($result, JSON_PRETTY_PRINT);
