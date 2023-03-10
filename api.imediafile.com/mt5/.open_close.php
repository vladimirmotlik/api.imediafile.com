<?php
if($_SERVER['REMOTE_ADDR'] === "62.168.42.38" || $_SERVER['REMOTE_ADDR'] === "95.217.41.149"){
$login = $_GET['login'];
$from = $_GET['from'] . ' 00:00:00';
$to = $_GET['to'] . ' 23:59:59';

/* -------------------------------------------------------------------------- */
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$sql = "SELECT mt5_daily.Profit FROM mt5_daily WHERE mt5_daily.Login = $login 
AND mt5_daily.Datetime > UNIX_TIMESTAMP('$from') 
AND mt5_daily.Datetime < UNIX_TIMESTAMP('$to')
ORDER BY mt5_daily.Datetime ASC;";
$result = $mysqli->query($sql);

$first = null;
$last = null;
$c = 0;

while($row = mysqli_fetch_assoc($result)){
if($c == 0){
$first = $row['Profit'];
$c++;
}
$last = $row['Profit'];
}

die(json_encode(array('first' => $first, 'last' => $last)));
}
