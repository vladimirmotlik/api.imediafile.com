<?php

$http_origin = $_SERVER['HTTP_ORIGIN'];
if ($http_origin == "https://personal.bidaskbit.com/competition"){  
header("Access-Control-Allow-Origin: $http_origin");
}else{
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "cors_error")));
}

//header("Access-Control-Allow-Origin: https://land.triangle.markets");      
//header("Access-Control-Allow-Methods: GET");      
//header("Content-Type: application/json; charset=UTF-8"); 

$server = "localhost";
$uid = "api.imediafile.com";
$dtb = "api.imediafile.com";
$pwd = "6Wpf9Tjk76a3mX0T";

$noOfAllowedActions = 10000;


if(isset($_GET['initialization']) && $_GET['initialization'] == true){
/* -------------------------------------------------------------------------- */
$remoteIP = $_SERVER['REMOTE_ADDR'];
$timestamp = time();
$md5 = md5($timestamp . $remoteIP);
/* -------------------------------------------------------------------------- */
$c = new mysqli($server, $uid, $pwd, $dtb);
if($c->connect_errno){
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "database_error", "is_phone_valid" => null)));
}
/* -------------------------------------------------------------------------- */
$r = $c->query("INSERT INTO `phone_verification`(`ip`, `session`) VALUES ('$remoteIP', '$md5');");
if(!$r){
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "database_error", "is_phone_valid" => null)));
}
/* -------------------------------------------------------------------------- */
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "success", "response" => $md5, "is_phone_valid" => null)));
}
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
else if (isset($_GET['phoneNumber']) && isset($_GET['session'])) {
/* -------------------------------------------------------------------------- */
$remoteIP = $_SERVER['REMOTE_ADDR'];
$phone = $_GET['phoneNumber'];
$session = $_GET['session'];
/* -------------------------------------------------------------------------- */
if(!ctype_xdigit($session)){
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "session_contains_not_allowed_chars", "is_phone_valid" => null)));
}
/* -------------------------------------------------------------------------- */
if(!is_numeric($phone)){
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "phone_number_contains_not_allowed_chars", "is_phone_valid" => null)));
}
/* -------------------------------------------------------------------------- */
$c = new mysqli($server, $uid, $pwd, $dtb);
if($c->connect_errno){
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "database_error", "is_phone_valid" => null)));
}
/* -------------------------------------------------------------------------- */
$r = $c->query("SELECT `id` FROM `phone_verification` WHERE `session` = '$session';");
if(!$r){
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "database_error", "is_phone_valid" => null)));
}
if($r->num_rows === 0){
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "session_is_not_exist", "is_phone_valid" => null)));
}
$res = $r->fetch_assoc();
$id = $res['id'];
/* -------------------------------------------------------------------------- */
$r = $c->query("SELECT SUM(`counter`) AS `total` FROM `phone_verification` WHERE `ip` = '$remoteIP';");
if(!$r){
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "database_error", "is_phone_valid" => null)));
}
$res = $r->fetch_assoc();
$total = $res['total'];
/* -------------------------------------------------------------------------- */
if($total == null){
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "remote_ip_not_found", "is_phone_valid" => null)));
}
/* -------------------------------------------------------------------------- */
if($total > $noOfAllowedActions){
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "api_limit_exceeded", "is_phone_valid" => null)));
}
/* -------------------------------------------------------------------------- */
// elementary check before API using
if(strlen($phone) < 10 || strlen($phone) > 16){
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "success", "response" => $phone, "is_phone_valid" => "false")));
}
/* -------------------------------------------------------------------------- */
// check the cached values before executing API
$r = $c->query("SELECT `status` FROM `phone_verification_phones` WHERE phone = '$phone';");
if($r->num_rows != 0){
$res = $r->fetch_assoc();
$status = $res['status'];
if($status == 1){
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "success", "response" => $phone . " (cached)", "is_phone_valid" => "true")));
}else{
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "success", "response" => $phone . " (cached)", "is_phone_valid" => "false")));
}
}
/* -------------------------------------------------------------------------- */
// increment counter 
$r = $c->query("UPDATE `phone_verification` SET `counter`= (`counter` + 1) WHERE id = $id;");
/* -------------------------------------------------------------------------- */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://apilayer.net/api/validate?access_key=43c951fb406de239ef94e7c3a7e30048&number=$phone&country_code=&format=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$errors = curl_error($ch);
curl_close($ch);
$json = json_decode($result, true);
/* -------------------------------------------------------------------------- */
if($json['valid'] == 1){
$r = $c->query("INSERT INTO `phone_verification_phones`(`session_id`, `phone`, `status`) VALUES ($id, $phone, 1);");
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "success", "response" => $phone, "is_phone_valid" => "true")));
}else{
$r = $c->query("INSERT INTO `phone_verification_phones`(`session_id`, `phone`, `status`) VALUES ($id, $phone, 0);");
$c->close();
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "success", "response" => $phone, "is_phone_valid" => "false")));
}
/* -------------------------------------------------------------------------- */
} 
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
else {
die(json_encode(array("status_code" => http_response_code(), "status_msg" => "error", "response" => "mandatory_params_are_missing", "is_phone_valid" => null)));
}
?>