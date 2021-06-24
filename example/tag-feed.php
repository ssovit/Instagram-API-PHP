<?php
header("Content-Type: application/json");
include __DIR__."/../vendor/autoload.php";
$api = new \Sovit\Instagram\Api(["sessionid"=>"xxx"]);
$result = $api->getTagFeed("nature");
echo json_encode($result,JSON_PRETTY_PRINT);
