<?php
/* -------------------------------------------------------------------------- */
if($_SERVER['REMOTE_ADDR'] === "62.168.42.38" || $_SERVER['REMOTE_ADDR'] === "95.217.41.149"){
$login = $_GET['login'];
$from = $_GET['from'] . ' 00:00:00';
$to = $_GET['to'] . ' 23:59:59';
/* -------------------------------------------------------------------------- */
$accountArray = json_decode($login, true);
foreach($accountArray as $aa){
$resultArray[] = array('account' => $aa, 'spread' => null, 'volume' => null, 'first_day' => null, 'last_day' => null, 'equity' => null, 'balance' => null, 'profit' => null);
} 
/* -------------------------------------------------------------------------- */
$imp = implode(',', $accountArray);
/* -------------------------------------------------------------------------- */
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$sql = "SELECT mt5_deals.Login AS account, SUM(mt5_deals.Profit) AS profit, SUM((((mt5_deals.MarketAsk - mt5_deals.MarketBid) * (mt5_deals.Volume / 10000)) / mt5_deals.RateMargin)) AS spread, SUM(((mt5_deals.Volume / 10000) * mt5_deals.Price)) AS volume FROM mt5_deals WHERE mt5_deals.Login IN ($imp) AND mt5_deals.Time > '$from' AND mt5_deals.Time < '$to' AND mt5_deals.Symbol != '' GROUP BY mt5_deals.Login;";
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
for($i = 0; $i < count($resultArray); $i++){
if($resultArray[$i]['account'] == $row['account']){
$spread = null;
$volume = null;
$profit = null;
if(isset($row['spread'])){
$resultArray[$i]['spread'] = $row['spread'];
}
if(isset($row['volume'])){
$resultArray[$i]['volume'] = $row['volume'];
}
if(isset($row['profit'])){
$resultArray[$i]['profit'] = $row['profit'];
}
break;
}
}
/* -------------------------------------------------------------------------- */
}
/* -------------------------------------------------------------------------- */
// FIRST AND LAST REPORT
$sql = "SELECT mt5_daily.Profit, mt5_daily.Balance, mt5_daily.EquityPrevDay, mt5_daily.Login FROM mt5_daily WHERE mt5_daily.Login IN ($imp) AND mt5_daily.Datetime > UNIX_TIMESTAMP('$from') AND mt5_daily.Datetime < UNIX_TIMESTAMP('$to') ORDER BY mt5_daily.Login, mt5_daily.Datetime ASC;";
$result = $mysqli->query($sql);
while($row = mysqli_fetch_assoc($result)){
$res[$row['Login']][] = array('profit' => $row['Profit'], 'balance' => $row['Balance'], 'equity' => $row['EquityPrevDay']);
}
/* -------------------------------------------------------------------------- */
// JOIN VALUES
for($i = 0; $i < count($resultArray); $i++){
$account = $resultArray[$i]['account'];
$c = count($res[$account]);
if($c != 0){
$resultArray[$i]['first_day'] = $res[$account][0]['profit'];
$resultArray[$i]['last_day'] = $res[$account][$c - 1]['profit'];
$resultArray[$i]['balance'] = $res[$account][$c - 1]['balance'];
$resultArray[$i]['equity'] = $res[$account][$c - 1]['equity'];
}
}
die(json_encode($resultArray));
}
