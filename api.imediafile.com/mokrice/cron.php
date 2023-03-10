<?php
$baseUrl = 'https://api2.hiveos.farm/api/v2';
function doCurl($ch) {
    $res = curl_exec($ch);
    if ($res === false) {
        die('CURL error: '.curl_error($ch));
    }
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $res = json_decode($res, true);
    if ($code < 200 || $code >= 300) {
        throw new Exception($res['message'] ?? 'Response error');
    }
    return $res;
}

$token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcGkyLmhpdmVvcy5mYXJtIiwiaWF0IjoxNjM1Nzk0NDEwLCJleHAiOjE5NTExNTQ0MTAsIm5iZiI6MTYzNTc5NDQxMCwianRpIjo0Njg0NTI2OSwic3ViIjo0Njg0NTI2OX0.Guz9Ci9wIY0zRz8phcjNWlSRy1tOS6s7oHuvhb54w5s';

$ch = curl_init("$baseUrl/farms/1283879/workers");
curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER     => [
        "Authorization: Bearer $token"
    ],
    CURLOPT_RETURNTRANSFER => true,
]);
$res = doCurl($ch);
$workers = $res['data'];


/* -------------------------------------------------------------------------- */
// GET VALUE FROM LAST CHECK
$f2 = fopen("/var/www/api.imediafile.com/mokrice/.c", "r");
$lastCount = fread($f2, filesize("/var/www/api.imediafile.com/mokrice/.c"));
fclose($f2);
/* -------------------------------------------------------------------------- */
$counter = 0;
$rigNames = '';
foreach($workers as $a){
if(isset($a['stats']['gpus_online'])){
$counter++;
}else{
$rigNames .= ' ' . $a['name'];
}
}
if($lastCount > $counter){
$total = 20;
saveInputData("cron", array('sms' => true, 'total' => $total, 'online' => $counter, 'lastCount' => $lastCount, 'rigName' => $rigNames));
file_get_contents("http://62.168.42.38:9187/cgi/WebCGI?1500101=account=api&password=rcq2sXPB&port=2&destination=00420730581322&content=" . urlencode("RIG OFFLINE:" . $rigNames . " -> https://api.imediafile.com/mokrice/"));
file_get_contents("http://62.168.42.38:9187/cgi/WebCGI?1500101=account=api&password=rcq2sXPB&port=2&destination=00420602328542&content=" . urlencode("RIG OFFLINE:" . $rigNames . " -> https://api.imediafile.com/mokrice/"));
}
/* -------------------------------------------------------------------------- */
// SAVE VALUE FROM CURRENT CHECK
$f2 = fopen("/var/www/api.imediafile.com/mokrice/.c", "w");
fwrite($f2, $counter);
fclose($f2);
/* -------------------------------------------------------------------------- */
function saveInputData($msg, $v2) {
$f = fopen("/var/www/api.imediafile.com/mokrice/.DATA", "a");
$date = date("Y-m-d H:i:s");
fwrite($f, " ---------- " . $date . " ---------- ");
foreach ($v2 as $key => $value) {
@fwrite($f, "\n $key = '$value' ");
}
fwrite($f, "\n -----------------------------------------\n\n");
fclose($f);
}
/* -------------------------------------------------------------------------- */
