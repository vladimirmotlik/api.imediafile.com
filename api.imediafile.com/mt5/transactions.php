<?php
if($_SERVER['REMOTE_ADDR'] === '95.217.41.149'){
/* -------------------------------------------------------------------------- */
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$sql = "SELECT `Deal`, `Timestamp`, `Time`, `Login`, `Profit`, `Comment` FROM `mt5_deals` WHERE `Symbol` = '' ORDER BY Deal DESC;";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
$arr[] = array('transactionId' => $row['Deal'], 'timestamp' => $row['Timestamp'], 'time' => $row['Time'], 'login' => $row['Login'], 'profit' => $row['Profit'], 'comment' =>  $row['Comment']); 
}
die(json_encode($arr));
}
?>