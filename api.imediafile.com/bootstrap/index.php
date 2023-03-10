<?php
/* -------------------------------------------------------------------------- */
header('Access-Control-Allow-Origin: *');
/* -------------------------------------------------------------------------- */
header("Content-Type: text/html; charset=utf-8");
/* -------------------------------------------------------------------------- */
if (!isset($_SERVER['REMOTE_ADDR'])) {
    saveAccessData("client ip address not obtained");
    exit();
}
/* -------------------------------------------------------------------------- */
$token = "98a38416ebb073cb8d28f0c4054c4b50";
$userIP = $_SERVER['REMOTE_ADDR'];
/* -------------------------------------------------------------------------- */ 
$obj = json_decode(file_get_contents("http://api.ipstack.com/" . $userIP . "?access_key=" . $token), true);
$countryCode = $obj['country_code'];
/* -------------------------------------------------------------------------- */

$vars = array(
    "firstName" => "",
    "lastName" => "",
    "email" => "",
    "phoneNumber" => "",
    "countryCode" => $countryCode, // zmena nefunkcni numverify
    "userIp" => $userIP,
    "phoneCountryCode" => "",
    "languageCode" => 'EN',
    "thankYouPage" => "",
    "landingPageUrl" => "",
    "affiliateId" => "",
    "affiliateName" => "",
    "utm_source" => '',
    "utm_campaign" => '',
    "utm_medium" => '',
    "utm_content" => '',
    "utm_term" => '',
    "utm_category" => '',
    "utm_creative" => "",
    "utmSource" => '',
    "utmCampaign" => '',
    "utmMedium" => '',
    "utmContent" => '',
    "utmTerm" => '',
    "utmCategory" => '',
    "utmCreative" => "",
    "campaignName" => "",
    "campaignId" => "",
    "pageId" => "",
    "formId" => "",
    "adgroupId" => "",
    "adsetName" => "",
    "adId" => "",
    "adName" => "",
    "creativeId" => "",
    "zone" => "",
    "referrer" => "",
    "branId" => "",
    "brandName" => "",
);
/* -------------------------------------------------------------------------- */
// FILL VALUES FROM POST
foreach ($vars as $key => $val) {
    if (isset($_POST[$key]) && trim($_POST[$key]) !== '') {
        $vars[$key] = htmlspecialchars(trim($_POST[$key]));
    }
}
/* -------------------------------------------------------------------------- */
$referralSource = $vars['landingPageUrl'];
$referralSourceArray = explode("?", $referralSource);
/* -------------------------------------------------------------------------- */
// FILL VALUES FROM GET
if (count($referralSourceArray) == 2) {
    $r1 = $referralSourceArray[1];
    $r2 = explode("&", $r1);
    if (count($r2) > 0) {
        for ($i = 0; $i < count($r2); $i++) {
            $r3 = explode("=", $r2[$i]);
            if (count($r3) == 2) {
                if (array_key_exists($r3[0], $vars)) {
                    $vars[$r3[0]] = $r3[1];
                }
            }
        }
    }
}
/* -------------------------------------------------------------------------- */
// FILL VALUES FROM GET
if (count($referralSourceArray) == 2) {
    $r1 = $referralSourceArray[1];
    $r2 = explode("&amp;", $r1);
    if (count($r2) > 0) {
        for ($i = 0; $i < count($r2); $i++) {
            $r3 = explode("=", $r2[$i]);
            if (count($r3) == 2) {
                if (array_key_exists($r3[0], $vars)) {
                    $vars[$r3[0]] = $r3[1];
                }
            }
        }
    }
}
/* -------------------------------------------------------------------------- */
$utm_old = array("utm_source", "utm_campaign", "utm_medium", "utm_content", "utm_term", "utm_category", "utm_creative");
$utm_new = array("utmSource", "utmCampaign", "utmMedium", "utmContent", "utmTerm", "utmCategory", "utmCreative");
for ($i = 0; $i < count($utm_old); $i++) {
    if (trim($vars[$utm_new[$i]]) == "") { // pokud nema hodnotu
        $vars[$utm_new[$i]] = $vars[$utm_old[$i]];
    }
}
/* -------------------------------------------------------------------------- */
savePostData($vars);
/* -------------------------------------------------------------------------- */
sendToJakub($vars);
/* -------------------------------------------------------------------------- */
header("Location: " . $vars['thankYouPage']);
/* -------------------------------------------------------------------------- */

function sendToJakub($vars) {
    $token = array("accessToken" => "wqzVIhCy3KWQnyACzdN9u0Cd8fOEaFfFs");
    /* ---------------------------------------------------------------------- */
    $crm = explode(".", $vars['brandName']);
    $crmResult = "";
    for ($i = 0; $i < count($crm); $i++) {
        $crmResult .= ucfirst($crm[$i]);
    }
    $brandName = array("brandName" => $crmResult);
    /* ---------------------------------------------------------------------- */
    $re = array_merge($vars, $token);
    $re = array_merge($re, $brandName);
    /* ---------------------------------------------------------------------- */
    saveTestData(json_encode($re));

    $ch1 = curl_init("https://lds.pineal.eu/api/lead/bootstrap/");
    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch1, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, json_encode($re));
    $response = curl_exec($ch1);
    curl_close($ch1);

    $f = fopen(".POSTBACK.txt", "a");
    $date = date("Y-m-d H:i:s");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, " $date\n");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, "response: $response \n");
    fwrite($f, " --------------------------------------------------------------\n\n\n");
    fclose($f);
}

function saveTestData($par) {
    $f = fopen(".TEST.txt", "a");
    $date = date("Y-m-d H:i:s");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, "  $date \n");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, " $par\n");
    fwrite($f, " --------------------------------------------------------------\n\n\n");
    fclose($f);
}

function savePostData($vars) {
    $f = fopen(".POST.txt", "a");
    $date = date("Y-m-d H:i:s");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, " $date\n");
    fwrite($f, " ------------------------ POST --------------------------------\n");
    foreach ($_POST as $key => $value) {
        fwrite($f, "\n $key = '$value' ");
    }
    fwrite($f, "\n --------------------------------------------------------------\n");
    fwrite($f, " ------------------------ VARS ---------------------------------\n");
    fwrite($f, " --------------------------------------------------------------\n");
    foreach ($vars as $key => $value) {
        fwrite($f, "\n $key = '$value' ");
    }
    fwrite($f, "\n --------------------------------------------------------------\n\n\n");
    fclose($f);
}

function saveAccessData($par) {
    $f = fopen(".ACCESS.txt", "a");
    $date = date("Y-m-d H:i:s");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, "  $date \n");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, " $par\n");
    fwrite($f, " --------------------------------------------------------------\n\n\n");
    fclose($f);
}
