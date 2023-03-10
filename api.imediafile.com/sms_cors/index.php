<?php
header("Access-Control-Allow-Origin: https://land.bidaskbit.com");      
header("Access-Control-Allow-Methods: GET");      
header("Content-Type: application/json; charset=UTF-8"); 



if(isset($_GET['accessToken']) && $_GET['accessToken'] === 'QSXezMevDRXFQzLTDQ9BMtK2'){
/* -------------------------------------------------------------------------- */
if(!isset($_GET['message'])){
echo json_encode(array('result' => 'error', 'message' => 'Message is not set.'));
exit();
}
$message = $_GET['message'];
if(strlen(trim($message)) == 0){
echo json_encode(array('result' => 'error', 'message' => 'Message is empty.'));
exit();
}
if(strlen(trim($message)) > 160){
echo json_encode(array('result' => 'error', 'message' => 'Message length reached (160 chars).'));
exit();
}
/* -------------------------------------------------------------------------- */
if(!isset($_GET['phone'])){
echo json_encode(array('result' => 'error', 'message' => 'Phone is not set.'));
exit();
}
$phone = $_GET['phone'];
if(strlen(trim($phone)) == 0){
echo json_encode(array('result' => 'error', 'message' => 'Phone is empty.'));
exit();
}
if(!is_numeric($phone)){
echo json_encode(array('result' => 'error', 'message' => 'Phone is not numeric.'));
exit();
}
/* -------------------------------------------------------------------------- */
$data = "client_id=17994_ujxgxqjtmes8k0ck80wwcgccskoks4c8oowcw0co4gg8g0g0c&client_secret=5ch6qacjyy048044c4ccgc4k40go0s0w0008owgwkwsso8oogg&grant_type=client_credentials";
/* -------------------------------------------------------------------------- */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://app.gosms.eu/oauth/v2/token");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$errors = curl_error($ch);
curl_close($ch);
$json = json_decode($result, true);
$accessToken = $json['access_token'];

/* -------------------------------------------------------------------------- */
$data = '{"message": "' . $message . '", "recipients": ["' . $phone . '"], "channel": 344776}';
/* -------------------------------------------------------------------------- */

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://app.gosms.eu/api/v1/messages?access_token=" . $accessToken);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$errors = curl_error($ch);
curl_close($ch);
$json = json_decode($result, true);
/* -------------------------------------------------------------------------- */
echo json_encode(array('result' => 'success', 'message' => 'Message has been sent.'));
exit();
/* -------------------------------------------------------------------------- */
}else{
echo json_encode(array('result' => 'error', 'message' => 'Access token is not set.'));
exit();
}