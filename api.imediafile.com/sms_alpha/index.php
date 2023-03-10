<?php
header('Content-Type: application/json');
if(isset($_GET['accessToken']) && $_GET['accessToken'] === 'en8xPzGxx7cR3SAZ'){
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
// START INTERNAL SMS PROVIDER FOR DUBAI
$substr = substr($phone,0,5);
if($substr == '00971'){
$withoutZeros = substr($phone, 2);
$tt = file_get_contents("https://api.smsala.com/api/SendSMS?api_id=API906812024242&api_password=7XkWCoseLC&sms_type=P&encoding=T&sender_id=" . rawurlencode("VIRAROSI OU") . "&phonenumber=$withoutZeros&textmessage=" . rawurlencode($message));
$apt = json_decode($tt, true);

echo json_encode(array('result' => 'success', 'message' => 'Message has been sent.', 'details' => $apt['remarks']));
die();
}
// END INTERNAL SMS PROVIDER FOR DUBAI
/* -------------------------------------------------------------------------- */


/* -------------------------------------------------------------------------- */
// START BULKSMS PROVIDER FOR SA
$substr = substr($phone,0,4);
if($substr == '0027'){
$phone = substr($phone,4);
$username = '4C0C942104E4446A90ADD41658931CC4-01-9';
$password = '#ZQmmLIP29FFzVWNq9kfI8C4R9WzP';
$messages = array(array('to' => '+27' . $phone, 'body' => $message)); 
$result = send_message(json_encode($messages), 'https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30', $username, $password);
echo json_encode(array('result' => 'success', 'message' => 'Message has been sent.'));
die();
}
// END BULKSMS PROVIDER FOR SA
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
$data = '{"message": "' . $message . '", "recipients": ["' . $phone . '"], "channel": 344781}';
/* -------------------------------------------------------------------------- */
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://app.gosms.eu/api/v1/messages?access_token=" . $accessToken);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
doLog("STATUS", ['payload' => $result, 'code' => $httpcode], '/var/www/api.imediafile.com/sms_alpha/.DATA');
curl_close($ch);
if($httpcode == 400){
$json = json_decode($result, true);
$err = '';
if(isset($json['errors'])){
$err = implode(', ', $json['errors']);
}
die(json_encode(['result' => 'error', 'message' => $err]));
}

$json = json_decode($result, true);
/* -------------------------------------------------------------------------- */
echo json_encode(array('result' => 'success', 'message' => 'Message has been sent.'));
exit();
/* -------------------------------------------------------------------------- */
}else{
echo json_encode(array('result' => 'error', 'message' => 'Access token is not set.'));
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

function doLog($msg, $v1, $file)
{
    $f = fopen($file, "a");
    $date = date("Y-m-d H:i:s");
    fwrite($f, " -----\n");
    fwrite($f, " $date \n");
    fwrite($f, $_SERVER['REMOTE_ADDR'] . "\n");
    fwrite($f, " -----\n");
    fwrite($f, " $msg\n");
    fwrite($f, " -----\n");
    if (is_array($v1)) {
        foreach ($v1 as $key => $value) {
            @fwrite($f, "\n $key = '$value' ");
        }
    }
    fclose($f);
} 