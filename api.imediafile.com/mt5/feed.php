<?php
if(!in_array($_SERVER['REMOTE_ADDR'], ['62.168.42.38', '95.217.41.149']) || !isset($_REQUEST['from']) || !isset($_REQUEST['to'])) die('whitelist');
$mysqli2 = new mysqli('localhost', 'rates', 'Ka5aoslxe7jBjE7w', 'rates');
$sql = "SELECT `date`, `rates` FROM `history_rates` WHERE `date` >= '{$_REQUEST['from']}' AND `date` <= '{$_REQUEST['to']}'";
$result = $mysqli2->query($sql);
$rates = array();
while($row = mysqli_fetch_assoc($result)){
$rates[$row['date']] = array('rates' => json_decode($row['rates'], 1));
}
die(json_encode($rates));