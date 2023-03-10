<?php
//header('Access-Control-Allow-Origin: *'); 
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$allow = array(
    "62.168.42.38",
    "194.182.77.77",
    "217.16.188.46",
);

$access = false;
for ($i = 0; $i < count($allow); $i++) {
    if ($allow[$i] === $_SERVER['REMOTE_ADDR']) {
        $access = true;
        break;
    }
}
if (!$access) {
    $a = array("error" => "Not allowed IP address detected." . $_SERVER['REMOTE_ADDR']);
    echo json_encode($a);
    exit();
}


if (isset($_GET['number']) && isset($_GET['token']) && $_GET['token'] === "9dpr2Km9") {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://apilayer.net/api/validate?access_key=43c951fb406de239ef94e7c3a7e30048&number=" . $_GET['number'] . "&country_code=&format=1");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $errors = curl_error($ch);
    curl_close($ch);
    echo $result;
    exit();
}
?>