<?php
header("Content-Type: application/json");
include __DIR__."/../vendor/autoload.php";
$api = new \Sovit\Instagram\Api(["sessionid"=>"1392237513%3AytVgKnWKEKYh0v%3A15"]);
$result = $api->getTag("nature");
echo json_encode($result,JSON_PRETTY_PRINT);
