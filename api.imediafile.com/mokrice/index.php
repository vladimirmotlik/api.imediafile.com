<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300&display=swap" rel="stylesheet">
<style>
* {font-family: 'Hind Siliguri', sans-serif; text-align: center;}
table {border-collapse: collapse;}
td {border: 1px solid #ccc; padding: 2px 10px}
tr:nth-child(even) {
  background-color: #f2f2f2;
}
</style>

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

//echo '<pre>';
///var_dump($workers);
//echo '</pre>';


echo '<table><tr style="background-color: #ccc"><td>Address</td><td>Name</td><td>State</td><td>GPUs Online</td><td>GPUs Overheated</td><td>Watts</td></tr>';
foreach($workers as $a){
$state = '<span style="color:red;"><strong>OFF</strong></span>';
if($a['stats']['online']){
$state = '<span style="color:green;"><strong>ON</strong></span>';
}

$gpuOnline = 'n/a';
if(isset($a['stats']['gpus_online'])){
$gpuOnline = $a['stats']['gpus_online'];
if($gpuOnline != 8){
$gpuOnline = '<span style="color:red;"><strong>' . $gpuOnline . '</strong></span>';
}else{
$gpuOnline = '<span style="color:green;"><strong>' . $gpuOnline . '</strong></span>';
}
}

$gpuOverHeated = 'n/a';
if(isset($a['stats']['gpus_overheated'])){
$gpuOverHeated  = $a['stats']['gpus_overheated'];
}

echo '<tr><td>' . $a['ip_addresses'][0] . '</td><td>' . $a['name'] . '</td><td>' . $state . '</td><td>' . $gpuOnline . '</td><td>' . $gpuOverHeated . '</td><td>' . $a['stats']['power_draw'] . '</td></tr>';
}
echo '</table>';