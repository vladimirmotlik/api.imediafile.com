<?php
header('Content-Type: application/json; charset=utf-8');
/* -------------------------------------------------------------------------- */
if($_SERVER['REMOTE_ADDR'] === "62.168.42.38" || $_SERVER['REMOTE_ADDR'] === "95.217.41.149"){
/* -------------------------------------------------------------------------- */
// GET ECB RATES
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://payment.imediafile.com/datafeed/ecb/?token=J6dHjHxNYBBcKF2w&date=" . date('Y-m'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);
$ecbRates = array('date' => date('Y-m'), 'USDEUR' => $result['data']['result']['USDEUR'], 'USDCZK' => $result['data']['result']['USDCZK'], 'USDUSD' => 1);
/* -------------------------------------------------------------------------- */
// MT5M DATABASE CONNECTION
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$login_date = date('Y-m-d');
/* -------------------------------------------------------------------------- */
// GET TRADING ACCOUNT
$sqlLogins = "SELECT DISTINCT mt5_daily.Login AS 'TPA', mt5_daily.Currency AS CURRENCY 
FROM mt5_daily WHERE mt5_daily.`Group` NOT LIKE '%Test%' AND mt5_daily.`Group` LIKE '%3anglefx%' AND
UNIX_TIMESTAMP('$login_date 00:00:00') AND mt5_daily.Datetime < UNIX_TIMESTAMP('$login_date 23:59:59') AND mt5_daily.Currency IN ('EUR', 'USD', 'CZK') ORDER BY mt5_daily.Login;";
/* -------------------------------------------------------------------------- */
$resultLogin = $mysqli->query($sqlLogins);
/* -------------------------------------------------------------------------- */
// CREATE RESULT ARRAY
while($rowLogin = mysqli_fetch_assoc($resultLogin)){
$resultArray[$rowLogin['TPA']] = array(
'trading_account_currency' => $rowLogin['CURRENCY'],
'usd_spread' => 0, // OK 
'usd_volume' => 0, // OK 
'usd_realized_pnl' => 0, // OK 
'origin_realized_pnl' => 0, // OK 
'usd_unrealized_pnl' => 0, // OK 
'origin_unrealized_pnl' => 0, // OK 
'usd_start' => 0, // OK
'origin_start' => 0, // OK
'usd_end' => 0, // OK
'origin_end' => 0, // OK
'usd_equity' => 0, // OK
'origin_equity' => 0, // OK
'usd_balance' => 0, // OK
'origin_balance' => 0, //OK
'usd_swap' => 0, // OK
'origin_swap' => 0, // OK
'usd_credit_start' => 0, // OK
'origin_credit_start' => 0, // OK
'usd_credit_end' => 0,
'origin_credit_end' => 0,
'day_rate_not_found_counter' => 0, // OK 
'symbol_rate_not_found_counter' => 0, // OK 
'tpa_rate_not_found_counter' => 0, // OK 
'start_count_usd' => 0, // OK
);
}
$start = 0;
/* -------------------------------------------------------------------------- */
// DATES
$dateArray = array(
'fromDate' => $_GET['from'],
'toDate' => $_GET['to'], 
'fromDateTime' => $_GET['from'] . ' 00:00:00',
'toDateTime' => $_GET['to'] . ' 23:59:59', 
'fromDateTimePlusOneDayStart' => date('Y-m-d H:i:s', strtotime($_GET['from'] . ' 00:00:00' . '+1day')), 
'fromDateTimePlusOneDayEnd' => date('Y-m-d H:i:s', strtotime($_GET['from'] . ' 23:59:59' . '+1day')), 
'toDateTimePlusOneDayStart' => date('Y-m-d H:i:s', strtotime($_GET['to'] . ' 00:00:00' . '+1day')),
'toDateTimePlusOneDayEnd' => date('Y-m-d H:i:s', strtotime($_GET['to'] . ' 23:59:59' . '+1day')),
'fromDatePlusOneDay' => explode(' ', date('Y-m-d H:i:s', strtotime($_GET['from'] . ' 00:00:00' . '+1day')))[0],
'toDatePlusOneDay' => explode(' ', date('Y-m-d H:i:s', strtotime($_GET['to'] . ' 23:59:59' . '+1day')))[0],
);
/* -------------------------------------------------------------------------- */
// GET HISTORY RATES TO ARRAY
$mysqli2 = new mysqli('localhost', 'rates', 'Ka5aoslxe7jBjE7w', 'rates');
$sql = "SELECT `date`, `rates` FROM `history_rates` WHERE `date` >= '" . $dateArray['fromDate'] . "'";
$result = $mysqli2->query($sql);
$rates = array();
while($row = mysqli_fetch_assoc($result)){
$rates[$row['date']] = array('rates' => $row['rates']);
}
/* -------------------------------------------------------------------------- */
// QUERY
$sql = "SELECT 
mt5_deals.Login AS 'TPA', 
mt5_deals.Time AS 'DT',
IF(mt5_deals.Entry = 1,(((mt5_deals.MarketAsk - mt5_deals.MarketBid) * (mt5_deals.ContractSize / mt5_deals.Volume))), 0) AS 'SPREAD',
IF(mt5_deals.Entry = 0,((mt5_deals.ContractSize / mt5_deals.Volume) * mt5_deals.Price), 0) AS 'VOLUME',
mt5_deals.Profit AS 'PROFIT',
mt5_deals.Storage AS 'SWAP'
FROM mt5_deals 
JOIN mt5_symbols ON (mt5_deals.Symbol = mt5_symbols.Symbol)
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol != ''
AND mt5_users.`Group` NOT LIKE '%Test%'
AND mt5_users.`Group` LIKE '%3anglefx%'
AND mt5_deals.Time > '" . $_GET['from'] . " 00:00:00' 
AND mt5_deals.Time < '" . $_GET['to'] . " 23:59:59';";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
// TRADING ACCOUNT CURRENCY
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
/* -------------------------------------------------------------------------- */
$dayRate = json_decode($rates[$_GET['to']]['rates'], true);
/* -------------------------------------------------------------------------- */
// CHECK IF SYMBOL RATE EXIST
if(!isset($dayRate['USD' . $accountCurrency])){
$resultArray[$row['TPA']]['symbol_rate_not_found_counter']++;
continue;
}
$symbolRate = $dayRate['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
// CHECK IF TRADING PLATFORM ACCOUNT RATE EXIST
if(!isset($dayRate['USD' . $accountCurrency])){
$resultArray[$row['TPA']]['tpa_rate_not_found_counter']++;
continue;
}
$tpaRate = $dayRate['USD' . $accountCurrency];

// ADDED AT 09.09.2022
$ecbRate = $ecbRates['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
// SPREAD IN USD
$resultArray[$row['TPA']]['usd_spread'] += ($row['SPREAD'] / $symbolRate);
// VOLUME IN USD
$resultArray[$row['TPA']]['usd_volume'] += ($row['VOLUME'] / $symbolRate);
// REALIZED PNL IN USD
$resultArray[$row['TPA']]['usd_realized_pnl'] += ($row['PROFIT'] / $ecbRate) + ($row['SWAP'] / $ecbRate);
// REALIZED PNL IN TRADING PLATFORM ACCOUNT CURRENCY
$resultArray[$row['TPA']]['origin_realized_pnl'] += ($row['PROFIT'] + $row['SWAP']);
// SWAP IN USD
$resultArray[$row['TPA']]['usd_swap'] += ($row['SWAP'] / $ecbRate);
// SWAP IN TRADING PLATFORM ACCOUNT CURRENCY
$resultArray[$row['TPA']]['origin_swap'] += $row['SWAP'];
}
/* -------------------------------------------------------------------------- */
// OPEN PROFIT
$sql = "SELECT 
mt5_positions.Login AS 'TPA', 
SUM(mt5_positions.Profit + mt5_positions.`Storage`) AS 'OPEN_PROFIT' 
FROM mt5_positions 
JOIN mt5_users ON (mt5_positions.Login = mt5_users.Login) 
WHERE mt5_users.`Group` NOT LIKE '%Test%' AND mt5_users.`Group` LIKE '%3anglefx%' GROUP BY mt5_positions.Login;";
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
// TRADING ACCOUNT CURRENCY
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
/* -------------------------------------------------------------------------- */
// ACTUAL DATE!
$dayRate = json_decode($rates[$_GET['to']]['rates'], true);
/* -------------------------------------------------------------------------- */
// CHECK IF TRADING PLATFORM ACCOUNT RATE EXIST
if(!isset($dayRate['USD' . $accountCurrency])){
$resultArray[$row['TPA']]['tpa_rate_not_found_counter']++;
continue;
}
$tpaRate = $dayRate['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
// UNREALIZED PNL IN USD
$resultArray[$row['TPA']]['usd_unrealized_pnl'] = ($row['OPEN_PROFIT'] / $tpaRate);
// UNREALIZED PNL IN TRADING PLATFORM ACCOUNT CURRENCY
$resultArray[$row['TPA']]['origin_unrealized_pnl'] = $row['OPEN_PROFIT'];
}
/* -------------------------------------------------------------------------- */
// UPDATE FROM 7.9.2022 - SWITCH TO SHIFT OR DONT SHIFT DATE  - IN DEFAULT WE HAVE TO SHIFT DATE + 1 DAY
$start_from = date('Y-m-d', strtotime($_GET['from'] . ' 00:00:00' . '+1day'));
if(isset($_REQUEST['date_shift']) && $_REQUEST['date_shift'] == 1){
$start_from = $_GET['from'];
}
/* -------------------------------------------------------------------------- */
// START LOSS
$sql = "SELECT 
mt5_daily.Profit + mt5_daily.ProfitStorage AS 'START_PROFIT_LOSS', 
IF((mt5_daily.Credit - mt5_daily.ProfitEquity) > 0, (mt5_daily.Credit - mt5_daily.ProfitEquity), 0)  AS 'CREDIT',
mt5_daily.Login AS 'TPA' 
FROM mt5_daily WHERE mt5_daily.`Group` NOT LIKE '%Test%' AND mt5_daily.`Group` LIKE '%3anglefx%' AND mt5_daily.Datetime > UNIX_TIMESTAMP('$start_from 00:00:00') AND mt5_daily.Datetime < UNIX_TIMESTAMP('$start_from 23:59:59');";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
// TRADING ACCOUNT CURRENCY
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
/* -------------------------------------------------------------------------- */
//$dayRate = json_decode($rates[$_GET['to']]['rates'], true);
/* -------------------------------------------------------------------------- */
// CHECK IF TRADING PLATFORM ACCOUNT RATE EXIST
//if(!isset($dayRate['USD' . $accountCurrency])){
//$resultArray[$row['TPA']]['tpa_rate_not_found_counter']++;
//continue;
//}
//$tpaRate = $dayRate['USD' . $accountCurrency];
$tpaRate = $ecbRates['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
// START LOSS DATE IN USD + 1 day
$resultArray[$row['TPA']]['usd_start'] = ($row['START_PROFIT_LOSS'] / $tpaRate);
$start += $resultArray[$row['TPA']]['usd_start'];
// START LOSS DATE IN TRADING PLATFORM ACCOUNT CURRENCY + 1 day
$resultArray[$row['TPA']]['origin_start'] = $row['START_PROFIT_LOSS'];
// START CREDIT IN USD + 1 day
$resultArray[$row['TPA']]['usd_credit_start'] = ($row['CREDIT'] / $tpaRate);
// START CREDIT IN TRADING PLATFORM ACCOUNT CURRENCY + 1 day
$resultArray[$row['TPA']]['origin_credit_start'] = $row['CREDIT'];
}
/* -------------------------------------------------------------------------- */
$end_to = date('Y-m-d', strtotime($_GET['to'] . ' 00:00:00' . '+1day'));
/* -------------------------------------------------------------------------- */
// END LOSS
$sql = "SELECT 
mt5_daily.Balance AS 'BALANCE', 
mt5_daily.EquityPrevDay AS 'EQUITY', 
mt5_daily.Profit + mt5_daily.ProfitStorage AS 'END_PROFIT_LOSS', 
IF((mt5_daily.Credit - mt5_daily.ProfitEquity) > 0, (mt5_daily.Credit - mt5_daily.ProfitEquity), 0)  AS 'CREDIT',
mt5_daily.Login AS 'TPA' 
FROM mt5_daily WHERE mt5_daily.`Group` NOT LIKE '%Test%' AND mt5_daily.`Group` LIKE '%3anglefx%' AND mt5_daily.Datetime > UNIX_TIMESTAMP('".$dateArray['toDateTimePlusOneDayStart']."') AND mt5_daily.Datetime < UNIX_TIMESTAMP('".$dateArray['toDateTimePlusOneDayEnd']."');";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
// TRADING ACCOUNT CURRENCY
$accountCurrency = $resultArray[$row['TPA']]['trading_account_currency'];
/* -------------------------------------------------------------------------- */
//$dayRate = json_decode($rates[$_GET['to']]['rates'], true);
/* -------------------------------------------------------------------------- */
// CHECK IF TRADING PLATFORM ACCOUNT RATE EXIST
//if(!isset($dayRate['USD' . $accountCurrency])){
//$resultArray[$row['TPA']]['tpa_rate_not_found_counter']++;
//continue;
//}
//$tpaRate = $dayRate['USD' . $accountCurrency];
$tpaRate = $ecbRates['USD' . $accountCurrency];
/* -------------------------------------------------------------------------- */
// END LOSS DATE IN USD + 1 day
$resultArray[$row['TPA']]['usd_end'] = ($row['END_PROFIT_LOSS'] / $tpaRate);
// END LOSS DATE IN TRADING PLATFORM ACCOUNT CURRENCY + 1 day
$resultArray[$row['TPA']]['origin_end'] = $row['END_PROFIT_LOSS'];
// END BALANCE IN USD + 1 day
$resultArray[$row['TPA']]['usd_balance'] = ($row['BALANCE'] / $tpaRate);
// END BALANCE IN TRADING PLATFORM ACCOUNT CURRENCY + 1 day
$resultArray[$row['TPA']]['origin_balance'] = $row['BALANCE'];
// END EQUITY IN USD + 1 day
$resultArray[$row['TPA']]['usd_equity'] = ($row['BALANCE'] / $tpaRate) + $resultArray[$row['TPA']]['usd_start'];
// END EQUITY IN TRADING PLATFORM ACCOUNT CURRENCY + 1 day
$resultArray[$row['TPA']]['origin_equity'] = $row['BALANCE'] + $resultArray[$row['TPA']]['origin_start'];
// END CREDIT IN USD + 1 day
$resultArray[$row['TPA']]['usd_credit_end'] = ($row['CREDIT'] / $tpaRate);
// END CREDIT IN TRADING PLATFORM ACCOUNT CURRENCY + 1 day
$resultArray[$row['TPA']]['origin_credit_end'] = $row['CREDIT'];
}
/* -------------------------------------------------------------------------- */
$resultArray['meta_data']['rates'] = $ecbRates;
$resultArray['meta_data']['is_shifted'] = $_REQUEST['date_shift'];
$resultArray['meta_data']['start_loss'] = date('Y-m-d', strtotime($start_from . ' 00:00:00' . '-1day'));
$resultArray['meta_data']['end_loss'] = date('Y-m-d', strtotime($end_to . ' 00:00:00' . '-1day'));
/* -------------------------------------------------------------------------- */
die(json_encode($resultArray));
/* -------------------------------------------------------------------------- */
}
/* -------------------------------------------------------------------------- */
function str_contains($haystack, $needle) {
return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}
/* -------------------------------------------------------------------------- */
