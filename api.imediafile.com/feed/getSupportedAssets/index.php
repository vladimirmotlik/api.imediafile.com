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
$supportedAssets = array('BTC', 'ETH', 'LTC', 'BCH', 'ADA', 'XRP', 'SOL', 'ETC');
/* -------------------------------------------------------------------------- */
$arr = array("status" => "OK", "message" => "OK", 'assets' => $supportedAssets);
die(json_encode($arr));



?>