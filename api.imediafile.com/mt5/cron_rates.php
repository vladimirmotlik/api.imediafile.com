<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://feed.excoin.cz/current/?token=66sYyqNuY6MejdEw");
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$json = json_decode($response, true);
curl_close($ch);
$d = date('Y-m-d');
$msg = json_encode($json['message']);
$mysqli = new mysqli('localhost', 'rates', 'Ka5aoslxe7jBjE7w', 'rates');
$sql = "INSERT INTO `history_rates`(`date`, `rates`) VALUES ('$d','$msg');";
$result = $mysqli->query($sql);