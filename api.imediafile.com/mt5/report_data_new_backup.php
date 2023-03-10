<?php




if(isset($_REQUEST['view']) && $_REQUEST['view'] == 1){
header('Content-Type: text/html; charset=utf-8');
if (empty($_SERVER['PHP_AUTH_USER']) ||
     $_SERVER['PHP_AUTH_USER'] != "joe_test" ||
     $_SERVER['PHP_AUTH_PW'] != "RNDD8eUdHdfPZqTB") {
    header('WWW-Authenticate: Basic realm="JOE_REPORTS"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'ACCESS_DENIED';
    exit;
}


?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300&display=swap" rel="stylesheet">
<style>
table {border-collapse: collapse;}
td {border: 1px solid #ccc; padding: 1px; text-align: center;}
tr:nth-child(even) {background-color: #f2f2f2;}
.header {background-color: #333; color: white;}
* {font-family: 'Hind Siliguri', sans-serif; font-size: 13px;}
</style>

<?php
} else {
header('Content-Type: application/json; charset=utf-8');

$isTrusted = false;
if($_SERVER['REMOTE_ADDR'] === "62.168.42.38" || $_SERVER['REMOTE_ADDR'] === "95.217.41.149" || $_SERVER['REMOTE_ADDR'] === "65.21.225.219" || $_SERVER['REMOTE_ADDR'] === "77.240.99.229"){
$isTrusted = true;
}
if(!$isTrusted)die('WHITELIST_ERROR');
}
/* -------------------------------------------------------------------------- */
if(!isset($_REQUEST['brand'])) die('PARAM_ERROR');
/* -------------------------------------------------------------------------- */

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://payment.imediafile.com/mt5/getTradingGroups/?token=5RD46vWp4aCCGrnn&platform_type=live&brand={$_REQUEST['brand']}&platform=MT5");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$r = curl_exec($ch);
if(curl_getinfo($ch, CURLINFO_HTTP_CODE) != 200) die('BRAND_ERROR');
$data = json_decode($r, true);
if (json_last_error() !== JSON_ERROR_NONE) die('JSON_ERROR');
if($data['status'] === 'ERROR') die('DATA_ERROR');

$str = ' IN (';
foreach($data['data'] as $row){
if(!str_contains($row['group'], 'test') && !str_contains($row['group'], 'Test') && !str_contains($row['group'], 'TEST')){
$str .= "'" . str_replace('\\', '\\\\', str_replace('%20', ' ', $row['group'])) . "',";
}
}
$str = substr($str, 0,-1);
$str .= ") ";
/* -------------------------------------------------------------------------- */
$nativeFrom = $_GET['from'];
$nativeTo = $_GET['to'];
/* -------------------------------------------------------------------------- */
$startDate = $nativeFrom;
if(weekend($startDate) == 7){ // SUNDAY
$startDate = date('Y-m-d', strtotime($startDate . '-2day')); 
} else if(weekend($startDate) == 6){ // SATURDAY
$startDate = date('Y-m-d', strtotime($startDate . '-1day')); 
} else if(weekend($startDate) == 1){ // MONDAY
$startDate = date('Y-m-d', strtotime($startDate . '-3day')); 
} else { //TU, WE, TH, FR
$startDate = $startDate;
}
/* -------------------------------------------------------------------------- */
$endDate = $nativeTo;
if(weekend($endDate) == 7){ // SUNDAY - FRIDAY DATA
$endDate = date('Y-m-d', strtotime($endDate . '-1day')); 
} else if(weekend($endDate) == 6){ // SATURDAY  - FRIDAY DATA
$endDate = $endDate;
} else { // MO, TU, WE, TH, FR
$endDate = date('Y-m-d', strtotime($endDate . '+1day')); 
}
/* -------------------------------------------------------------------------- */
$startDateActual = $nativeFrom;
if(weekend($startDateActual) == 7){ // SUNDAY
$startDateActual  = date('Y-m-d', strtotime($startDateActual . '-3day')); 
} else if(weekend($startDateActual) == 6){ // SATURDAY
$startDateActual  = date('Y-m-d', strtotime($startDateActual . '-2day')); 
} else if(weekend($startDateActual) == 1){ // MONDAY
$startDateActual = date('Y-m-d', strtotime($startDateActual . '-4day')); 
} else { //TU, WE, TH, FR
$startDateActual = $startDateActual;
}
/* -------------------------------------------------------------------------- */
$endDate = $nativeTo;
if(weekend($endDate) == 7){ // SUNDAY - FRIDAY DATA
$endDate = date('Y-m-d', strtotime($endDate . '-1day')); 
} else if(weekend($endDate) == 6){ // SATURDAY  - FRIDAY DATA
$endDate = $endDate;
} else { // MO, TU, WE, TH, FR
$endDate = date('Y-m-d', strtotime($endDate . '+1day')); 
}
/* -------------------------------------------------------------------------- */
$endDateActual = $nativeTo;
if(weekend($endDateActual) == 7){ // SUNDAY - FRIDAY DATA
$endDateActual = date('Y-m-d', strtotime($endDateActual . '-2day')); 
} if(weekend($endDateActual) == 6){ // SATURDAY - FRIDAY DATA
$endDateActual = date('Y-m-d', strtotime($endDateActual . '-1day')); 
} else { // MO, TU, WE, TH, FR
$endDateActual = $endDateActual;
}
/* -------------------------------------------------------------------------- */


// GET RATES FROM END DAY
$ecbDate = explode('-', $endDate);
// GET ECB RATES
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://payment.imediafile.com/datafeed/ecb/?token=J6dHjHxNYBBcKF2w&date=" . $ecbDate[0] . '-' . $ecbDate[1]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);
/* -------------------------------------------------------------------------- */
// ECB RATES ALL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://payment.imediafile.com/datafeed/ecb/?token=J6dHjHxNYBBcKF2w");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$ecbRatesAll = json_decode(curl_exec($ch), true);
curl_close($ch);
/* -------------------------------------------------------------------------- */
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
// NEWEST RATES
$mysqli2 = new mysqli('localhost', 'rates', 'Ka5aoslxe7jBjE7w', 'rates');
$sql = "SELECT `date`, `rates` FROM `history_rates` ORDER BY id DESC LIMIT 1";
$result = $mysqli2->query($sql);
$row = mysqli_fetch_assoc($result);
$newestRate = json_decode($row['rates'], 1);
/* -------------------------------------------------------------------------- */
$login_date = date('Y-m-d');
$sqlLogins = "SELECT DISTINCT mt5_daily.Login AS 'TPA', mt5_daily.Currency AS CURRENCY, mt5_daily.Group AS GR FROM mt5_daily WHERE mt5_daily.`Group` $str AND
UNIX_TIMESTAMP('$login_date 00:00:00') AND mt5_daily.Datetime < UNIX_TIMESTAMP('$login_date 23:59:59') AND mt5_daily.Currency IN ('EUR', 'USD', 'CZK', 'ZAR') ORDER BY mt5_daily.Login DESC;";
/* -------------------------------------------------------------------------- */
$resultLogin = $mysqli->query($sqlLogins);
/* -------------------------------------------------------------------------- */
while($rowLogin = mysqli_fetch_assoc($resultLogin)){
$resultArray[$rowLogin['TPA']] = array(
'trading_account_currency' => $rowLogin['CURRENCY'], 'trading_group' => $rowLogin['GR'], 'usd_realized_pnl' => 0, 'origin_realized_pnl' => 0,
'usd_realized_pnl_plus_swap' => 0,'origin_realized_pnl_plus_swap' => 0,'usd_start' => 0,'origin_start' => 0,
'usd_end' => 0, 'origin_end' => 0, 'usd_equity' => 0,'origin_equity' => 0,'usd_balance' => 0,'origin_balance' => 0,
'usd_swap' => 0,'origin_swap' => 0,'usd_credit_start' => 0,'origin_credit_start' => 0,'usd_credit_end' => 0,
'origin_credit_end' => 0,'usd_dividend' => 0,'origin_dividend' => 0, 'usd_commission' => 0,'origin_commission' => 0, 
'usd_adjustment' => 0,'origin_adjustment' => 0, 'usd_final_pnl' => 0,'origin_final_pnl' => 0,'usd_spread' => 0,'origin_spread' => 0,
'usd_deposit' => 0,'origin_deposit' => 0,'usd_withdraw' => 0,'origin_withdraw' => 0, 'usd_fd' => 0, 'origin_fd' => 0, 'fd_time' => 'n/a',
'usd_nb' => 0, 'origin_nb' => 0
);
}
/* -------------------------------------------------------------------------- */
// REALIZED + SWAP
$sql = "SELECT 
mt5_deals.Login AS 'TPA', mt5_deals.Time AS 'DT', 
mt5_deals.Profit AS 'PROFIT', mt5_deals.Storage AS 'SWAP', 
mt5_deals.Commission AS 'COMMISSION', 
((mt5_deals.MarketAsk - mt5_deals.MarketBid) * mt5_deals.ContractSize * (mt5_deals.Volume / 10000)) / 2 AS 'SPREAD',
mt5_symbols.CurrencyProfit AS 'SPREAD_CURRENCY'
FROM mt5_deals JOIN mt5_symbols ON (mt5_deals.Symbol = mt5_symbols.Symbol) JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol != '' AND mt5_users.`Group` $str AND  mt5_deals.Time > '" . $startDateActual . " 00:00:00' AND mt5_deals.Time < '" . $endDateActual . " 23:59:59';";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
$ecbRateDate = explode('-', explode(' ', $row['DT'])[0]);
$ecbRate = $ecbRatesAll['data']['result'][$ecbRateDate[0] . '-' . $ecbRateDate[1]]['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
if(!isset($newestRate['USD' . strtoupper($row['SPREAD_CURRENCY'])])){
continue;
}
$usdSpread = $row['SPREAD'] / $newestRate['USD' . strtoupper($row['SPREAD_CURRENCY'])];

$resultArray[$row['TPA']]['usd_realized_pnl'] += ($row['PROFIT'] / $ecbRate);
$resultArray[$row['TPA']]['origin_realized_pnl'] += $row['PROFIT'];
$resultArray[$row['TPA']]['usd_realized_pnl_plus_swap'] += ($row['PROFIT'] / $ecbRate) + ($row['SWAP'] / $ecbRate);
$resultArray[$row['TPA']]['origin_realized_pnl_plus_swap'] += ($row['PROFIT'] + $row['SWAP']);
$resultArray[$row['TPA']]['usd_swap'] += ($row['SWAP'] / $ecbRate);
$resultArray[$row['TPA']]['origin_swap'] += $row['SWAP'];
$resultArray[$row['TPA']]['usd_commission'] += ($row['COMMISSION'] / $ecbRate);
$resultArray[$row['TPA']]['origin_commission'] += $row['COMMISSION'];
$resultArray[$row['TPA']]['usd_spread'] += $usdSpread;
$resultArray[$row['TPA']]['origin_spread'] += $usdSpread;
}
/* -------------------------------------------------------------------------- */
// DIVIDEND / ADJUSTMENT / NEGATIVE BALANCE REMOVAL
$sql = "SELECT 
mt5_deals.Login AS 'TPA', mt5_deals.Comment AS 'COMMENT', mt5_deals.Time AS 'DT', mt5_deals.Profit AS 'PROFIT' FROM mt5_deals JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol = '' AND mt5_users.`Group` $str AND mt5_deals.Time > '" . $startDateActual . " 00:00:00' AND mt5_deals.Time < '" . $endDateActual . " 23:59:59';";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
$ecbRateDate = explode('-', explode(' ', $row['DT'])[0]);
$ecbRate = $ecbRatesAll['data']['result'][$ecbRateDate[0] . '-' . $ecbRateDate[1]]['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
if(str_contains($row['COMMENT'], 'di_')){
$resultArray[$row['TPA']]['usd_dividend'] += ($row['PROFIT'] / $ecbRate);
$resultArray[$row['TPA']]['origin_dividend'] += $row['PROFIT'];
}
if(str_contains($row['COMMENT'], 'ad_')){
$resultArray[$row['TPA']]['usd_adjustment'] += ($row['PROFIT'] / $ecbRate);
$resultArray[$row['TPA']]['origin_adjustment'] += $row['PROFIT'];
}
if(str_contains($row['COMMENT'], 'nb_')){
$resultArray[$row['TPA']]['usd_nb'] += ($row['PROFIT'] / $ecbRate);
$resultArray[$row['TPA']]['origin_nb'] += $row['PROFIT'];
}
}
/* -------------------------------------------------------------------------- */
// START LOSS
$sql = "SELECT FROM_UNIXTIME(mt5_daily.Datetime) AS DT, mt5_daily.Profit + mt5_daily.ProfitStorage AS 'START_PROFIT_LOSS', mt5_daily.Credit AS 'CREDIT', mt5_daily.Login AS 'TPA' 
FROM mt5_daily WHERE mt5_daily.`Group` $str AND mt5_daily.Datetime > UNIX_TIMESTAMP('$startDate 00:00:00') AND mt5_daily.Datetime < UNIX_TIMESTAMP('$startDate 23:59:59');";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
//$ecbRateDate = explode('-', explode(' ', $row['DT'])[0]);
$ecbRateDate = explode('-', $nativeFrom);
$ecbRate = $ecbRatesAll['data']['result'][$ecbRateDate[0] . '-' . $ecbRateDate[1]]['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
$resultArray[$row['TPA']]['usd_start'] = ($row['START_PROFIT_LOSS'] / $ecbRate);
$resultArray[$row['TPA']]['origin_start'] = $row['START_PROFIT_LOSS'];
$resultArray[$row['TPA']]['usd_credit_start'] = ($row['CREDIT'] / $ecbRate);
$resultArray[$row['TPA']]['origin_credit_start'] = $row['CREDIT'];
}
/* -------------------------------------------------------------------------- */
// END LOSS
$sql = "SELECT FROM_UNIXTIME(mt5_daily.Datetime) AS DT, mt5_daily.Balance AS 'BALANCE', mt5_daily.EquityPrevDay AS 'EQUITY', mt5_daily.Profit + mt5_daily.ProfitStorage AS 'END_PROFIT_LOSS', mt5_daily.Credit AS 'CREDIT', mt5_daily.Login AS 'TPA' 
FROM mt5_daily WHERE mt5_daily.`Group` $str AND mt5_daily.Datetime > UNIX_TIMESTAMP('$endDate 00:00:00') AND mt5_daily.Datetime < UNIX_TIMESTAMP('$endDate 23:59:59');";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
//$ecbRateDate = explode('-', explode(' ', $row['DT'])[0]);
$ecbRateDate = explode('-', $nativeTo);
$ecbRate = $ecbRatesAll['data']['result'][$ecbRateDate[0] . '-' . $ecbRateDate[1]]['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
$resultArray[$row['TPA']]['usd_end'] = ($row['END_PROFIT_LOSS'] / $ecbRate);
$resultArray[$row['TPA']]['origin_end'] = $row['END_PROFIT_LOSS'];
$resultArray[$row['TPA']]['usd_balance'] = ($row['BALANCE'] / $ecbRate);
$resultArray[$row['TPA']]['origin_balance'] = $row['BALANCE'];
$resultArray[$row['TPA']]['usd_equity'] = ($row['BALANCE'] / $ecbRate) + $resultArray[$row['TPA']]['usd_end'];
$resultArray[$row['TPA']]['origin_equity'] = $row['BALANCE'] + $resultArray[$row['TPA']]['origin_end'];
$resultArray[$row['TPA']]['usd_credit_end'] = ($row['CREDIT'] / $ecbRate);
$resultArray[$row['TPA']]['origin_credit_end'] = $row['CREDIT'];
}
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
/* -------------------------------------------------------------------------- */
foreach($resultArray as $key => $val){
$resultArray[$key]['usd_final_pnl'] = (($val['usd_end'] - $val['usd_start']) + $val['usd_realized_pnl_plus_swap'] + $val['usd_dividend'] + $val['usd_commission'] + $val['usd_adjustment'] + $val['usd_nb']);
$resultArray[$key]['origin_final_pnl'] = (($val['origin_end'] - $val['origin_start']) + $val['origin_realized_pnl_plus_swap'] + $val['origin_dividend'] + $val['origin_commission'] + $val['origin_adjustment'] + $val['origin_nb']);
}

// DEPOSIT
$sql = "SELECT
mt5_deals.Login AS 'TPA',
SUM(mt5_deals.Profit) AS 'DEPOSIT'
FROM mt5_deals
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE Symbol = '' AND mt5_users.`Group` $str AND ACTION = 2 AND Profit != 0 AND
(
mt5_deals.`COMMENT` NOT LIKE '%Rollover%' AND
mt5_deals.`COMMENT` NOT LIKE '%Credit In%' AND
mt5_deals.`COMMENT` NOT LIKE '%Credit Out%' AND
mt5_deals.`COMMENT` NOT LIKE '%Dividend%' AND
mt5_deals.`COMMENT` NOT LIKE '%Adjustment%' AND
mt5_deals.`COMMENT` NOT LIKE '%Charge%' AND
mt5_deals.`COMMENT` NOT LIKE '%Reimbursement%' AND
mt5_deals.`COMMENT` NOT LIKE '%Internal%' AND
mt5_deals.`COMMENT` NOT LIKE '%zero%' AND
mt5_deals.`COMMENT` NOT LIKE 'di_%' AND
mt5_deals.`COMMENT` NOT LIKE 'ad_%' AND
mt5_deals.`COMMENT` NOT LIKE 'td_%' AND
mt5_deals.`COMMENT` NOT LIKE 'bo_%' AND
mt5_deals.`COMMENT` NOT LIKE 'if_%' AND
mt5_deals.`COMMENT` NOT LIKE 'nb_%' AND
mt5_deals.`COMMENT` NOT LIKE '%Test%'
) AND mt5_deals.Profit > 0 AND mt5_deals.Time > '$startDateActual 00:00:00' AND mt5_deals.Time < '$endDateActual 23:59:59' 
GROUP BY mt5_deals.Login";

$result = $mysqli->query($sql);
while($row = mysqli_fetch_assoc($result)){
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
if($accountCurrency === null){
continue;
}
$ecbRate = $ecbRatesAll['data']['result'][date("Y") . '-' . date('m')]['USD' . $accountCurrency];
$resultArray[$row['TPA']]['origin_deposit'] += $row['DEPOSIT'];
$resultArray[$row['TPA']]['usd_deposit'] += $row['DEPOSIT'] / $ecbRate;
}

// WITHDRAW
$sql = "SELECT
mt5_deals.Login AS 'TPA',
SUM(mt5_deals.Profit) AS 'WITHDRAW'
FROM mt5_deals
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE Symbol = '' AND mt5_users.`Group` $str AND ACTION = 2 AND Profit != 0 AND 
(
mt5_deals.`COMMENT` NOT LIKE '%Rollover%' AND
mt5_deals.`COMMENT` NOT LIKE '%Credit In%' AND
mt5_deals.`COMMENT` NOT LIKE '%Credit Out%' AND
mt5_deals.`COMMENT` NOT LIKE '%Dividend%' AND
mt5_deals.`COMMENT` NOT LIKE '%Adjustment%' AND
mt5_deals.`COMMENT` NOT LIKE '%Charge%' AND
mt5_deals.`COMMENT` NOT LIKE '%Reimbursement%' AND
mt5_deals.`COMMENT` NOT LIKE '%Internal%' AND
mt5_deals.`COMMENT` NOT LIKE '%zero%' AND
mt5_deals.`COMMENT` NOT LIKE 'di_%' AND
mt5_deals.`COMMENT` NOT LIKE 'ad_%' AND
mt5_deals.`COMMENT` NOT LIKE 'td_%' AND
mt5_deals.`COMMENT` NOT LIKE 'bo_%' AND
mt5_deals.`COMMENT` NOT LIKE 'if_%' AND
mt5_deals.`COMMENT` NOT LIKE 'nb_%' AND
mt5_deals.`COMMENT` NOT LIKE '%Test%'
) AND mt5_deals.Profit < 0 AND mt5_deals.Time > '$startDateActual 00:00:00' AND mt5_deals.Time < '$endDateActual 23:59:59' 
GROUP BY mt5_deals.Login";

$result = $mysqli->query($sql);
while($row = mysqli_fetch_assoc($result)){
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
if($accountCurrency === null){
continue;
}
$ecbRate = $ecbRatesAll['data']['result'][date("Y") . '-' . date('m')]['USD' . $accountCurrency];
$resultArray[$row['TPA']]['origin_withdraw'] += $row['WITHDRAW'];
$resultArray[$row['TPA']]['usd_withdraw'] += $row['WITHDRAW'] / $ecbRate;
}


// FIRST DEPOSIT
$sql = "SELECT
mt5_deals.Login AS 'TPA',
mt5_deals.Profit AS 'FIRST_DEPOSIT',
MIN(mt5_deals.Time) AS 'DT'
FROM mt5_deals
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE Symbol = '' AND mt5_users.`Group` $str AND ACTION = 2 AND Profit != 0 AND
(
mt5_deals.`COMMENT` NOT LIKE '%Rollover%' AND
mt5_deals.`COMMENT` NOT LIKE '%Credit In%' AND
mt5_deals.`COMMENT` NOT LIKE '%Credit Out%' AND
mt5_deals.`COMMENT` NOT LIKE '%Dividend%' AND
mt5_deals.`COMMENT` NOT LIKE '%Adjustment%' AND
mt5_deals.`COMMENT` NOT LIKE '%Charge%' AND
mt5_deals.`COMMENT` NOT LIKE '%Reimbursement%' AND
mt5_deals.`COMMENT` NOT LIKE '%Internal%' AND
mt5_deals.`COMMENT` NOT LIKE '%zero%' AND
mt5_deals.`COMMENT` NOT LIKE 'di_%' AND
mt5_deals.`COMMENT` NOT LIKE 'ad_%' AND
mt5_deals.`COMMENT` NOT LIKE 'td_%' AND
mt5_deals.`COMMENT` NOT LIKE 'bo_%' AND
mt5_deals.`COMMENT` NOT LIKE 'if_%' AND
mt5_deals.`COMMENT` NOT LIKE 'nb_%' AND
mt5_deals.`COMMENT` NOT LIKE '%Test%'
) AND mt5_deals.Profit > 0 AND mt5_deals.Time > '2020-09-01 00:00:00' AND mt5_deals.Time < '$endDateActual 23:59:59' 
GROUP BY mt5_deals.Login;";

$result = $mysqli->query($sql);
while($row = mysqli_fetch_assoc($result)){
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
if($accountCurrency === null){
continue;
}
if($row['FIRST_DEPOSIT'] == 0){
//die($row['FIRST_DEPOSIT']);
continue;
}


$ecbRate = $ecbRatesAll['data']['result'][date("Y") . '-' . date('m')]['USD' . $accountCurrency];
$resultArray[$row['TPA']]['origin_fd'] = $row['FIRST_DEPOSIT'];
$resultArray[$row['TPA']]['usd_fd'] = $row['FIRST_DEPOSIT'] / $ecbRate;


$resultArray[$row['TPA']]['fd_time'] = $row['DT'];
}
/* -------------------------------------------------------------------------- */
if(isset($_REQUEST['view']) && $_REQUEST['view'] == 1){
echo '<table><tr class="header"><td>account</td><td>currency</td><td>spread</td><td>realized_pnl(usd/tac)</td><td>realized_pnl_plus_swap(usd/tac)</td><td>start(usd/tac)</td><td>end(usd/tac)</td><td>equity(usd/tac)</td>
<td>balance(usd/tac)</td><td>swap(usd/tac)</td><td>credit_start(usd/tac)</td><td>credit_end(usd/tac)</td><td>dividend(usd/tac)</td><td>commission(usd/tac)</td><td>adjustment(usd/tac)</td><td>deposit(usd/tac)</td>
<td>FD(usd/tac)</td><td>FD time</td><td>withdraw(usd/tac)</td><td>negative balance(usd/tac)</td><td>FINAL_PNL_SWAP(usd/tac)</td></tr>';
foreach($resultArray as $key => $val){

//if($key != '12148'){
//continue;
//}

if(
$val['origin_realized_pnl'] == 0 && 
$val['origin_realized_pnl_plus_swap'] == 0 &&
$val['origin_start'] == 0 &&
$val['origin_end'] == 0 &&
$val['origin_equity'] == 0 &&
$val['origin_balance'] == 0 &&
$val['origin_swap'] == 0 &&
$val['origin_credit_start'] == 0 &&
$val['origin_credit_end'] == 0 &&
$val['origin_dividend'] == 0 &&
$val['origin_commission'] == 0 &&
$val['origin_adjustment'] == 0
){
continue;
}


echo '<tr>'; 
echo '<td>' . $key . '</td>';
echo '<td>' . $val['trading_account_currency'] . '</td>';
echo '<td style="background:rgba(208, 53, 53, .1);">' . round($val['usd_spread'],2) . '/' . round($val['origin_spread'],2) . '</td>';
echo '<td>' . round($val['usd_realized_pnl'],2) . '/' . round($val['origin_realized_pnl'],2) . '</td>';
echo '<td>' . round($val['usd_realized_pnl_plus_swap'],2) . '/' . round($val['origin_realized_pnl_plus_swap'],2) . '</td>';
echo '<td>' . round($val['usd_start'],2) . '/' . round($val['origin_start'],2) . '</td>';
echo '<td>' . round($val['usd_end'],2) . '/' . round($val['origin_end'],2) . '</td>';
echo '<td>' . round($val['usd_equity'],2) . '/' . round($val['origin_equity'],2) . '</td>';
echo '<td>' . round($val['usd_balance'],2) . '/' . round($val['origin_balance'],2)  . '</td>';
echo '<td>' . round($val['usd_swap'],2) . '/' . round($val['origin_swap'],2) . '</td>';
echo '<td>' . round($val['usd_credit_start'],2) . '/' . round($val['origin_credit_start'],2) . '</td>';
echo '<td>' . round($val['usd_credit_end'],2) . '/' . round($val['origin_credit_end'],2) . '</td>';
echo '<td>' . round($val['usd_dividend'],2) . '/' . round($val['origin_dividend'],2) . '</td>';
echo '<td>' . round($val['usd_commission'],2) . '/' . round($val['origin_commission'],2) . '</td>';
echo '<td>' . round($val['usd_adjustment'],2) . '/' . round($val['origin_adjustment'],2) . '</td>';
echo '<td>' . round($val['usd_deposit'],2) . '/' . round($val['origin_deposit'],2) . '</td>';
echo '<td>' . round($val['usd_fd'],2) . '/' . round($val['origin_fd'],2) . '</td>';
echo '<td>' . $val['fd_time'] . '</td>';
echo '<td>' . round($val['usd_withdraw'],2) . '/' . round($val['origin_withdraw'],2) . '</td>';
echo '<td>' . round($val['usd_nb'],2) . '/' . round($val['origin_nb'],2) . '</td>';
echo '<td style="background:rgba(51, 170, 51, .1); font-weight: bold;">' . round($val['usd_final_pnl'] ,2) . ' / ' . round($val['origin_final_pnl'] ,2)  . '</td>';
echo '</tr>';
}
echo '</table>';
die();
}
die(json_encode($resultArray));
/* -------------------------------------------------------------------------- */
function str_contains($haystack, $needle) {
return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}
/* -------------------------------------------------------------------------- */
function weekend($date) {
    $currentDate = new DateTime($date, new DateTimeZone("Europe/Prague"));
    return $currentDate->format('N');
}
/* -------------------------------------------------------------------------- */
