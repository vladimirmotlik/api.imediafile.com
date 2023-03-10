<?php
if($_SERVER['REMOTE_ADDR'] == '62.168.42.38'){
/* -------------------------------------------------------------------------- */
header('Content-Type: application/json; charset=utf-8');
/* -------------------------------------------------------------------------- */
$token = "98a38416ebb073cb8d28f0c4054c4b50";
$obj = json_decode(file_get_contents("http://api.ipstack.com/" . $_REQUEST['ip'] . "?access_key=" . $token), true);
/* -------------------------------------------------------------------------- */
die(json_encode($obj));
}