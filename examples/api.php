<?php

include "../vendor/autoload.php";

use SocApi\SocApi;
use SocApi\Instagram;
use SocApi\ApiEndpoint;

$db = mysqli_connect("localhost", "root", "", "test");
$sapi = new SocApi($db);
$sapi->addSocial(new Instagram($sapi));

$worker = new ApiEndpoint($sapi, [
    "maxPostsLoad" => 3,
]);
die(json_encode($worker($_POST)));
