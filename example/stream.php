<?php
header("Content-Type: application/json");
include __DIR__."/../vendor/autoload.php";
$api = new \Sovit\Instagram\Api(["sessionid"=>"XXX"]);
$result = $api->getShortcode("CMkEg0BDpAi");
$media=$result->graphql->shortcode_media->display_url;

$streamer=new \Sovit\Instagram\Stream();
$streamer->stream($media);
