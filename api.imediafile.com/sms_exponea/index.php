<?php
header('Content-Type: application/json');



if(!in_array($_SERVER['REMOTE_ADDR'], ['34.78.190.252','35.205.31.48', '62.168.42.38'])){
echo json_encode(array('result' => 'error', 'message' => 'Whitelist error.'));
exit();
}


if(isset($_GET['accessToken']) && $_GET['accessToken'] === 'abhruphrw78AsHNjgT62f6RZ'){
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
echo json_encode(array('result' => 'error', 'message' => 'number_not_set'));
exit();
}
$phone = $_GET['phone'];
if(strlen(trim($phone)) == 0){
echo json_encode(array('result' => 'error', 'message' => 'number_is_empty'));
exit();
}
if(!is_numeric($phone)){
echo json_encode(array('result' => 'error', 'message' => 'number_is_not_numeric'));
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
$data = '{"message": "' . $message . '", "recipients": ["' . $phone . '"], "channel": 377751}';
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
//var_dump($json);
/* -------------------------------------------------------------------------- */
$link = NULL;
if(isset($json['link'])){
$link = $json['link'];
}

$customerData = NULL;
if(isset($_REQUEST['customerData'])){
$customerData = $_REQUEST['customerData'];
}

/* -------------------------------------------------------------------------- */
$server = "localhost";
$uid = "api.imediafile.com";
$dtb = "api.imediafile.com";
$pwd = "6Wpf9Tjk76a3mX0T";
$c = new mysqli($server, $uid, $pwd, $dtb);
$r = $c->query("INSERT INTO `sms_exponea`(`cutomer_data`, `phone`, `message`, `external_id`) VALUES ('$customerData','$phone','$message','$link');");
/* -------------------------------------------------------------------------- */

echo json_encode(array('result' => 'success', 'message' => 'Message has been sent.'));
exit();
/* -------------------------------------------------------------------------- */
}else{
echo json_encode(array('result' => 'error', 'message' => 'token_error'));
exit();
}

function send_message ( $post_body, $url, $username, $password) {
$ch = curl_init( );
$headers = array('Content-Type:application/json','Authorization:Basic '. base64_encode("$username:$password"));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt ( $ch, CURLOPT_URL, $url );
curl_setopt ( $ch, CURLOPT_POST, 1 );
curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
$output = array();
$output['server_response'] = curl_exec( $ch );
$curl_info = curl_getinfo( $ch );
$output['http_status'] = $curl_info[ 'http_code' ];
$output['error'] = curl_error($ch);
curl_close( $ch );
return $output;
} 