<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
/* -------------------------------------------------------------------------- */
header('Content-Type: application/json');
/* -------------------------------------------------------------------------- */
$token = "aMMUf8XJugMxtcYz";
/* -------------------------------------------------------------------------- */
$allowedIPs = array("62.168.42.38");
/* -------------------------------------------------------------------------- */
// IP CHECK
$temp = false;
foreach ($allowedIPs as $a) {
if ($a == $_SERVER['REMOTE_ADDR']) {
$temp = true;
}
}
if (!$temp) {
$arr = array("status" => "ERROR", "message" => "WHITELIST ERROR");
die(json_encode($arr));
}
/* -------------------------------------------------------------------------- */
//TOKEN CHECK
if (!isset($_REQUEST["token"])) {
    $arr = array("status" => "ERROR", "message" => "TOKEN NOT SET");
    die(json_encode($arr));
}
/* -------------------------------------------------------------------------- */
// TOKEN VALUE CHECK
if ($_REQUEST["token"] !== $token) {
$arr = array("status" => "ERROR", "message" => "TOKEN MISMATCH.");
die(json_encode($arr));
}
/* -------------------------------------------------------------------------- */
$supportedAssets = array('BTC', 'ETH', 'LTC', 'BCH', 'ADA', 'XRP', 'SOL', 'ETC', 'DOGE', 'XLM', 'BNB', 'BSC', 'EGLD', 'MATIC', 'CELO', 'TRON', 'FLOW');
/* -------------------------------------------------------------------------- */
if(!isset($_REQUEST['asset'])){
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-CoinAPI-Key:45029DC4-AFAD-4936-A680-FA7904F47E29', 'Content-Type:application/json'));
curl_setopt($ch, CURLOPT_URL, "https://rest.coinapi.io/v1/assets/BTC;ETH;LTC;BCH;ADA;XRP;SOL;ETC;DOGE;XLM;BNB;BSC;EGLD;MATIC;CELO;TRON;FLOW");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
die($r);
}else{
if(isset($_REQUEST['asset']) && in_array($_REQUEST['asset'], $supportedAssets)){
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-CoinAPI-Key:45029DC4-AFAD-4936-A680-FA7904F47E29', 'Content-Type:application/json'));
curl_setopt($ch, CURLOPT_URL, "https://rest.coinapi.io/v1/assets/" . $_REQUEST['asset']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
die($r);
}else{
$arr = array("status" => "ERROR", "message" => "BAD_REQUEST");
die(json_encode($arr));
}
}
?>