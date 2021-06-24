<?php
header("Content-Type: application/json");
$session=require("./session.php");
include __DIR__."/../vendor/autoload.php";
$api = new \Sovit\Instagram\Api(["sessionid"=>$session]);
$result = $api->getTagFeed("nature");
echo json_encode($result,JSON_PRETTY_PRINT);
