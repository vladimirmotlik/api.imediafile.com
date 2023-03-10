<?php
/* -------------------------------------------------------------------------- */
echo getClientBalance();
/* -------------------------------------------------------------------------- */
function getClientBalance(){
/* -------------------------------------------------------------------------- */
// CTRADER API KEY
$requestData = array('hashedPassword' => "2ad705cbd95100a5485d41f05ae0df53", 'login' => 10001);
$post_json = json_encode($requestData);
$ch = curl_init();
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $post_json);
curl_setopt($ch, CURLOPT_URL, 'https://live-bidaskbit.webapi.ctrader.com:8443/v2/webserv/managers/token');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
/* -------------------------------------------------------------------------- */
$xml = simplexml_load_string($response); 
/* -------------------------------------------------------------------------- */
// GET BALANCE 
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://live-bidaskbit.webapi.ctrader.com:8443/v2/webserv/traders/?from=2020-01-01T00:00:00.000&to=2024-01-01T00:00:00.000&token=' . urlencode($xml->webservToken));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$xml = simplexml_load_string($response);

$is_exist = false;
$currency = null;
$login = null;

foreach($xml->trader as $a){
if($a->login == 6011509){
$is_exist = true;
$currency = $a->depositCurrency . '';
$login = $a->login . '';
}
}

curl_close($ch);
/* -------------------------------------------------------------------------- */
return json_encode(array($currency, $is_exist, $login));
/* -------------------------------------------------------------------------- */
}
?>