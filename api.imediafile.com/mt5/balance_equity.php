<?php
if($_SERVER['REMOTE_ADDR'] === '95.217.41.149' && isset($_GET['login'])){
/* -------------------------------------------------------------------------- */
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$sql = "SELECT Balance, Equity FROM `mt5_accounts` WHERE Login = " . $_GET['login'] . ";";
$result = $mysqli->query($sql);
$row = mysqli_fetch_assoc($result);
die($row['Balance'] . '|' . $row['Equity']);
}else {
die('not_authorized');
}
?>