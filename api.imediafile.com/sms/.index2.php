<?php
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
$data = '{"message": "Hello World!", "recipients": ["00420773795853"], "channel": 344776}';
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
var_dump($json);
/* -------------------------------------------------------------------------- */
?>