<?php
/* -------------------------------------------------------------------------- */
if($_SERVER['REMOTE_ADDR'] === "62.168.42.38" || $_SERVER['REMOTE_ADDR'] === "95.217.41.149"){
$login = $_GET['login'];
$from = $_GET['from'] . ' 00:00:00';
$to = $_GET['to'] . ' 23:59:59';
/* -------------------------------------------------------------------------- */
$accountArray = json_decode($login, true);
/* -------------------------------------------------------------------------- */
// GET HISTORY RATES ARRAY
$mysqli2 = new mysqli('localhost', 'rates', 'Ka5aoslxe7jBjE7w', 'rates');
$sql = "SELECT `date`, `rates` FROM `history_rates` WHERE `date` >= '".$_GET['from']."' AND `date` <= '".$_GET['to']."';";
$result = $mysqli2->query($sql);
$rates = array();
while($row = mysqli_fetch_assoc($result)){
$rates[] = array('date' => $row['date'], 'rates' => $row['rates']);
}
/* -------------------------------------------------------------------------- */
$imp = implode(',', $accountArray);
/* -------------------------------------------------------------------------- */
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$sql = "SELECT 
mt5_deals.Login AS 'TPA', 
mt5_symbols.CurrencyProfit AS 'CURRENCY', 
mt5_deals.Time AS 'DT',
IF(mt5_deals.Entry = 1,(((mt5_deals.MarketAsk - mt5_deals.MarketBid) * (mt5_deals.ContractSize / mt5_deals.Volume))), 0) AS 'SPREAD',
IF(mt5_deals.Entry = 0,((mt5_deals.ContractSize / mt5_deals.Volume) * mt5_deals.Price), 0) AS 'VOLUME',
mt5_deals.Profit AS 'PROFIT',
mt5_deals.Storage AS 'SWAP',
mt5_users.`Group` AS 'ACCOUNT_CURRENCY'
FROM mt5_deals 
JOIN mt5_symbols ON (mt5_deals.Symbol = mt5_symbols.Symbol)
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol != ''
AND mt5_deals.Login IN ($imp) 
AND mt5_users.`Group` NOT LIKE '%Test%'
AND mt5_deals.Time > '$from' 
AND mt5_deals.Time < '$to';";
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
// CURRENCIES TO USD BY RATE
$tempArray = array();
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
// ACCOUNT CURRENCY FOR CLOSE PROFIT
$ac = $row['ACCOUNT_CURRENCY'];
$acc = null;

if(str_contains($ac, 'USD')){
$acc = 'USD';
}
if(str_contains($ac, 'EUR')){
$acc = 'EUR';
}
if(str_contains($ac, 'CZK')){
$acc = 'CZK';
}
/* -------------------------------------------------------------------------- */
$ex = explode(' ', $row['DT'])[0];
$r1 = null;
foreach($rates as $r){
if($r['date'] == $ex){
$r1 = $r['rates'];
break;
}
}
/* -------------------------------------------------------------------------- */
if($r1 != null){
$r1 = json_decode($r1, true);
if(isset($r1['USD' . $row['CURRENCY']])){
$tempArray[] = array('TPA' => $row['TPA'], 'CURRENCY' => $row['CURRENCY'], 'DT' => $row['DT'], 'SPREAD' => $row['SPREAD'] / $r1['USD' . $row['CURRENCY']], 'VOLUME' => $row['VOLUME'] / $r1['USD' . $row['CURRENCY']], 'CLOSE_PROFIT' => $row['PROFIT'] / $r1['USD' . $acc], 'SWAP' => $row['SWAP'] / $r1['USD' . $acc]);
}
}
}
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
// OPEN PROFIT - OPEN_PNL
$sql = "SELECT 
mt5_positions.Login AS 'TPA',
SUM(mt5_positions.Profit) AS 'OPEN_PROFIT',
mt5_users.`Group` AS 'ACCOUNT_CURRENCY'
FROM mt5_positions
JOIN mt5_users ON (mt5_positions.Login = mt5_users.Login)
WHERE mt5_positions.Login IN ($imp) 
GROUP BY mt5_positions.Login;";
$result = $mysqli->query($sql);
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
// ACCOUNT CURRENCY FOR PROFIT
$ac = $row['ACCOUNT_CURRENCY'];
$acc = null;

if(str_contains($ac, 'USD')){
$acc = 'USD';
}
if(str_contains($ac, 'EUR')){
$acc = 'EUR';
}
if(str_contains($ac, 'CZK')){
$acc = 'CZK';
}
/* -------------------------------------------------------------------------- */
$openProfitArray[] = array('TPA' => $row['TPA'], 'OPEN_PROFIT' => $row['OPEN_PROFIT'], 'ACCOUNT_CURRENCY' => $acc);
/* -------------------------------------------------------------------------- */
}
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
// ADD (SUM) VALUES TO RESULT ARRAY
$resultArray = array();
foreach($tempArray as $ta){
$is_exist = false;
for($i = 0; $i < count($resultArray); $i++){
if($resultArray[$i]['account'] == $ta['TPA']){
$resultArray[$i]['spread'] += $ta['SPREAD']; // checked IF(mt5_deals.Entry = 1,(((mt5_deals.MarketAsk - mt5_deals.MarketBid) * (mt5_deals.ContractSize / mt5_deals.Volume))), 0) AS 'SPREAD'
$resultArray[$i]['volume'] += $ta['VOLUME']; // for each deal (entry = 0) -> (ContractSize / Volume) * Price  -> USD -> VOLUME ( shows client total amount in market represented in USD )
$resultArray[$i]['close_profit'] += $ta['CLOSE_PROFIT']; // close profit (pnl) from deals
$resultArray[$i]['swap'] += $ta['SWAP']; // checked by MT5M and Swapnil - calculated as sum of storage values
$is_exist = true;
break;
}
}
if(!$is_exist){
$resultArray[] = array('account' => $ta['TPA'], 'spread' => $ta['SPREAD'], 'volume' => $ta['VOLUME'], 'close_profit' => $ta['CLOSE_PROFIT'], 'open_profit' => null, 'first_day' => null, 'last_day' => null, 'equity' => null, 'balance' => null, 'swap' => $ta['SWAP'], 'credit_first_day' => null, 'credit_last_day' => null);
}
}
/* -------------------------------------------------------------------------- */
// ADD OPEN PROFIT TO RESULT ARRAY FROM GROUP QUERY
$last_rates = json_decode($rates[count($rates) - 1]['rates'], true);
foreach($openProfitArray as $opa){
for($i = 0; $i < count($resultArray); $i++){
if($resultArray[$i]['account'] == $opa['TPA']){
$resultArray[$i]['open_profit'] = $opa['OPEN_PROFIT'] / $last_rates['USD' . $opa['ACCOUNT_CURRENCY']]; //  open_profit (unrealized pnl is counted from mt5_positions table) 
break;
}
}
}
/* -------------------------------------------------------------------------- */
// FOR ACCOUNTS WHERE NOT BE AVAILABLE FROM FIRST QUERY
for($i = 0; $i < count($accountArray); $i++){
$is_exist = false;
foreach($resultArray as $ra){
if($ra['account'] == $accountArray[$i]){
$is_exist = true;
break;
}
}
if(!$is_exist){
$resultArray[] = array('account' => (String)$accountArray[$i], 'spread' => 0, 'volume' => 0, 'profit' => 0, 'first_day' => null, 'last_day' => null, 'equity' => null, 'balance' => null);
}
}
/* -------------------------------------------------------------------------- */
// FIRST AND LAST REPORT
$sql = "SELECT mt5_daily.Currency, mt5_daily.Profit, mt5_daily.Balance, mt5_daily.EquityPrevDay, mt5_daily.Credit, mt5_daily.Login FROM mt5_daily WHERE mt5_daily.Login IN ($imp) AND mt5_daily.Datetime > UNIX_TIMESTAMP('$from') AND mt5_daily.Datetime < UNIX_TIMESTAMP('$to') ORDER BY mt5_daily.Login, mt5_daily.Datetime ASC;";
$result = $mysqli->query($sql);
while($row = mysqli_fetch_assoc($result)){
$res[$row['Login']][] = array('profit' => $row['Profit'], 'balance' => $row['Balance'], 'equity' => $row['EquityPrevDay'], 'currency' => $row['Currency'], 'credit_first_day' => $row['Credit'] - $row['EquityPrevDay'], 'credit_last_day' => $row['Credit'] - $row['EquityPrevDay']);
}
/* -------------------------------------------------------------------------- */
// LAST RATES
//$last_rates = json_decode($rates[count($rates) - 1]['rates'], true);
/* -------------------------------------------------------------------------- */
// JOIN VALUES TO ORIGINAL ARRAY
for($i = 0; $i < count($resultArray); $i++){
$account = $resultArray[$i]['account'];
/* -------------------------------------------------------------------------- */
$c = count($res[$account]);
if($c != 0){
/* -------------------------------------------------------------------------- */
// CREDIT COUNT
$credit_start = $res[$account][0]['credit_first_day'];
if($credit_start <= 0){
$credit_start = 0;
} 

$credit_last = $res[$account][$c - 1]['credit_last_day'];
if($credit_last <= 0){
$credit_last = 0;
} 
/* -------------------------------------------------------------------------- */
$resultArray[$i]['credit_first_day'] = $credit_start / $last_rates['USD' . $res[$account][$c - 1]['currency']];
$resultArray[$i]['credit_last_day'] = $credit_last / $last_rates['USD' . $res[$account][$c - 1]['currency']];
$resultArray[$i]['first_day'] = $res[$account][0]['profit'] / $last_rates['USD' . $res[$account][$c - 1]['currency']];
$resultArray[$i]['last_day'] = $res[$account][$c - 1]['profit'] / $last_rates['USD' . $res[$account][$c - 1]['currency']];
$resultArray[$i]['balance'] = $res[$account][$c - 1]['balance']  / $last_rates['USD' . $res[$account][$c - 1]['currency']];
$resultArray[$i]['equity'] = $res[$account][$c - 1]['equity'] / $last_rates['USD' . $res[$account][$c - 1]['currency']];
}
}
/* -------------------------------------------------------------------------- */
die(json_encode($resultArray));
/* -------------------------------------------------------------------------- */
}
/* -------------------------------------------------------------------------- */
function str_contains($haystack, $needle) {
return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}
/* -------------------------------------------------------------------------- */
