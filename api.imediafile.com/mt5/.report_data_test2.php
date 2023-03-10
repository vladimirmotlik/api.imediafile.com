<style>
table {border-collapse: collapse;}
td {border: 1px solid #ccc; padding: 5px;}
</style>
<?php
/* -------------------------------------------------------------------------- */
// GET ECB RATES
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://payment.imediafile.com/datafeed/ecb/?token=J6dHjHxNYBBcKF2w&date=" . date('Y-m'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = json_decode(curl_exec($ch), true);
curl_close($ch);
$ecbRates = array('date' => date('Y-m'), 'USDEUR' => $result['data']['result']['USDEUR'], 'USDCZK' => $result['data']['result']['USDCZK'], 'USDUSD' => 1);
var_dump($ecbRates);
/* -------------------------------------------------------------------------- */
// MT5M DATABASE CONNECTION
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$login_date = date('Y-m-d');
/* -------------------------------------------------------------------------- */
$sql_logins = "SELECT DISTINCT mt5_daily.Login AS 'TPA', mt5_daily.Currency AS CURRENCY 
FROM mt5_daily WHERE mt5_daily.`Group` NOT LIKE '%Test%' AND mt5_daily.`Group` LIKE '%3anglefx%' AND
UNIX_TIMESTAMP('$login_date 00:00:00') AND mt5_daily.Datetime < UNIX_TIMESTAMP('$login_date 23:59:59') AND mt5_daily.Currency IN ('EUR', 'USD', 'CZK') ORDER BY mt5_daily.Login;";
/* -------------------------------------------------------------------------- */
$result_login = $mysqli->query($sql_logins);
/* -------------------------------------------------------------------------- */
while($row_login = mysqli_fetch_assoc($result_login)){

if($row_login['CURRENCY'] == 'USD'){ 
$tpaRate = 1; 
}

if($row_login['CURRENCY'] == 'EUR'){ 
$tpaRate = 0.9959117821343385;
}

if($row_login['CURRENCY'] == 'CZK'){ 
$tpaRate = 23.613011;
}


$arr[$row_login['TPA']] = array(
'nature_upnl_start' => 0, 
'converted_upnl_start' => 0, 
'nature_upnl_end' => 0, 
'converted_upnl_end' => 0,
'rate' => $tpaRate, 
'currency' => $row_login['CURRENCY'], 
'nature_credit_start' => 0, 
'converted_credit_start' => 0, 
'nature_credit_end' => 0, 
'converted_credit_end' => 0, 
'nature_profit' => 0, 
'converted_profit' =>0, 
'nature_swap' => 0, 
'converted_swap' => 0,
'nature_cpnl' => 0, 
'converted_cpnl' => 0,
'nature_commission' => 0,
'converted_commission' => 0,
'nature_adjustment' => 0,
'converted_adjustment' => 0,
'nature_nbr' => 0,
'converted_nbr' => 0,
'nature_ifee' => 0,
'converted_ifee' => 0,
);
}
/* -------------------------------------------------------------------------- */
$sql_start = "SELECT 
mt5_daily.Profit + mt5_daily.ProfitStorage AS 'START_PROFIT_LOSS', 
IF((mt5_daily.Credit - mt5_daily.ProfitEquity) > 0, (mt5_daily.Credit - mt5_daily.ProfitEquity), 0)  AS 'CREDIT',
mt5_daily.Login AS 'TPA' FROM mt5_daily WHERE mt5_daily.`Group` NOT LIKE '%Test%' AND mt5_daily.`Group` LIKE '%3anglefx%' AND 
mt5_daily.Datetime > UNIX_TIMESTAMP('2022-07-28 00:00:00') AND mt5_daily.Datetime < UNIX_TIMESTAMP('2022-07-28 23:59:59') ORDER BY mt5_daily.Login;";
/* -------------------------------------------------------------------------- */
$result_start = $mysqli->query($sql_start);
/* -------------------------------------------------------------------------- */
while($row_start = mysqli_fetch_assoc($result_start)){
$arr[$row_start['TPA']]['nature_upnl_start'] = $row_start['START_PROFIT_LOSS'];
$arr[$row_start['TPA']]['converted_upnl_start'] = $row_start['START_PROFIT_LOSS'] / $arr[$row_start['TPA']]['rate'];
$arr[$row_start['TPA']]['nature_credit_start'] = $row_start['CREDIT'];
$arr[$row_start['TPA']]['converted_credit_start'] = $row_start['CREDIT'] / $arr[$row_start['TPA']]['rate'];
}
/* -------------------------------------------------------------------------- */
$sql_end = "SELECT 
mt5_daily.Profit + mt5_daily.ProfitStorage AS 'END_PROFIT_LOSS', 
IF((mt5_daily.Credit - mt5_daily.ProfitEquity) > 0, (mt5_daily.Credit - mt5_daily.ProfitEquity), 0)  AS 'CREDIT',
mt5_daily.Login AS 'TPA' FROM mt5_daily WHERE mt5_daily.`Group` NOT LIKE '%Test%' AND mt5_daily.`Group` LIKE '%3anglefx%' AND 
mt5_daily.Datetime > UNIX_TIMESTAMP('2022-08-30 00:00:00') AND mt5_daily.Datetime < UNIX_TIMESTAMP('2022-08-30 23:59:59') ORDER BY mt5_daily.Login;";
/* -------------------------------------------------------------------------- */
$result_end = $mysqli->query($sql_end);
/* -------------------------------------------------------------------------- */
while($row_end = mysqli_fetch_assoc($result_end)){
$arr[$row_end['TPA']]['nature_upnl_end'] = $row_end['END_PROFIT_LOSS'];
$arr[$row_end['TPA']]['converted_upnl_end'] = $row_end['END_PROFIT_LOSS'] / $arr[$row_end['TPA']]['rate'];
$arr[$row_end['TPA']]['nature_credit_end'] = $row_end['CREDIT'];
$arr[$row_end['TPA']]['converted_credit_end'] = $row_end['CREDIT'] / $arr[$row_end['TPA']]['rate'];
}
/* -------------------------------------------------------------------------- */
$sql_profit_swap = "SELECT 
mt5_deals.Login AS 'TPA', 
SUM(mt5_deals.Profit) AS 'PROFIT',
SUM(mt5_deals.Storage) AS 'SWAP'
FROM mt5_deals 
JOIN mt5_symbols ON (mt5_deals.Symbol = mt5_symbols.Symbol)
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol != ''
AND mt5_users.`Group` NOT LIKE '%Test%'
AND mt5_users.`Group` LIKE '%3anglefx%'
AND mt5_deals.Time > '2022-07-29 00:00:00' 
AND mt5_deals.Time < '2022-08-29 23:59:59'
GROUP BY mt5_deals.Login ORDER BY mt5_deals.Login;";
/* -------------------------------------------------------------------------- */
$result_profit_swap = $mysqli->query($sql_profit_swap);
/* -------------------------------------------------------------------------- */
while($row_profit_swap = mysqli_fetch_assoc($result_profit_swap)){
$arr[$row_profit_swap['TPA']]['nature_profit'] = $row_profit_swap['PROFIT'];
$arr[$row_profit_swap['TPA']]['converted_profit'] = $row_profit_swap['PROFIT'] / $arr[$row_profit_swap['TPA']]['rate'];
$arr[$row_profit_swap['TPA']]['nature_swap'] = $row_profit_swap['SWAP'];
$arr[$row_profit_swap['TPA']]['converted_swap'] = $row_profit_swap['SWAP'] / $arr[$row_profit_swap['TPA']]['rate'];
$arr[$row_profit_swap['TPA']]['nature_cpnl'] = $row_profit_swap['PROFIT'] + $row_profit_swap['SWAP'];
$arr[$row_profit_swap['TPA']]['converted_cpnl'] = ($row_profit_swap['PROFIT'] + $row_profit_swap['SWAP']) / $arr[$row_profit_swap['TPA']]['rate'];
}
/* -------------------------------------------------------------------------- */
$sql_adjustment = "SELECT 
mt5_deals.Login AS 'TPA', 
SUM(mt5_deals.Profit) AS 'PROFIT'
FROM mt5_deals 
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol = ''
AND mt5_deals.Comment LIKE 'ad%'
AND mt5_users.`Group` NOT LIKE '%Test%'
AND mt5_users.`Group` LIKE '%3anglefx%'
AND mt5_deals.Time > '2022-07-29 00:00:00' 
AND mt5_deals.Time < '2022-08-29 23:59:59'
GROUP BY mt5_deals.Login ORDER BY mt5_deals.Login;";
/* -------------------------------------------------------------------------- */
$result_adjustment = $mysqli->query($sql_adjustment);
/* -------------------------------------------------------------------------- */
while($row_adjustment = mysqli_fetch_assoc($result_adjustment)){
$arr[$row_adjustment['TPA']]['nature_adjustment'] = $row_adjustment['PROFIT'];
$arr[$row_adjustment['TPA']]['converted_adjustment'] = $row_adjustment['PROFIT'] / $arr[$row_adjustment['TPA']]['rate'];
}
/* -------------------------------------------------------------------------- */
$sql_nbr = "SELECT 
mt5_deals.Login AS 'TPA', 
SUM(mt5_deals.Profit) AS 'PROFIT'
FROM mt5_deals 
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol = ''
AND mt5_deals.Comment LIKE 'nb%'
AND mt5_users.`Group` NOT LIKE '%Test%'
AND mt5_users.`Group` LIKE '%3anglefx%'
AND mt5_deals.Time > '2022-07-29 00:00:00' 
AND mt5_deals.Time < '2022-08-29 23:59:59'
GROUP BY mt5_deals.Login ORDER BY mt5_deals.Login;";
/* -------------------------------------------------------------------------- */
$result_nbr = $mysqli->query($sql_nbr);
/* -------------------------------------------------------------------------- */
while($row_nbr = mysqli_fetch_assoc($result_nbr)){
$arr[$row_nbr['TPA']]['nature_nbr'] = $row_nbr['PROFIT'];
$arr[$row_nbr['TPA']]['converted_nbr'] = $row_nbr['PROFIT'] / $arr[$row_nbr['TPA']]['rate'];
}
/* -------------------------------------------------------------------------- */
echo "<table><tr>
<td>TA</td>
<td>CURR.</td>
<td>RATE</td>
<td>NATURE UPNL START</td>
<td>USD UPNL START</td>
<td>NATURE UPNL END</td>
<td>USD UPNL END</td>
<td>NATURE CREDIT START</td>
<td>USD CREDIT START</td>
<td>NATURE CREDIT END</td>
<td>USD CREDIT END</td>
<td>NATURE PROFIT</td>
<td>USD PROFIT</td>
<td>NATURE SWAP</td>
<td>USD SWAP</td>
<td>NATURE CPNL</td>
<td>USD CPNL</td>
<td>NATURE COMMISSION</td>
<td>USD COMMISSION</td>
<td>NATURE ADJUSTMENT</td>
<td>USD ADJUSTMENT</td>
<td>NATURE NBR</td>
<td>USD NBR</td>
<td>NATURE IFEE</td>
<td>USD IFEE</td>
<td>NATURE PNL</td>
<td>USD PNL</td>
</tr>';";


foreach($arr as $a => $b){


$usd = 
($arr[$a]['converted_upnl_end'] - $arr[$a]['converted_upnl_start']) 
+ ($arr[$a]['converted_credit_end'] - $arr[$a]['converted_credit_start']) 
+ $arr[$a]['converted_profit'] + $arr[$a]['converted_swap'] 
+ $arr[$a]['converted_commission'] 
+ $arr[$a]['converted_adjustment'] 
+ $arr[$a]['converted_nbr'] 
+ $arr[$a]['converted_ifee'];

$nature = 
($arr[$a]['nature_upnl_end'] - $arr[$a]['nature_upnl_start']) 
+ ($arr[$a]['nature_credit_end'] - $arr[$a]['nature_credit_start']) 
+ $arr[$a]['nature_profit'] + $arr[$a]['nature_swap'] 
+ $arr[$a]['nature_commission'] 
+ $arr[$a]['nature_adjustment'] 
+ $arr[$a]['nature_nbr'] 
+ $arr[$a]['nature_ifee'];


echo "<tr>
<td>" . $a . "</td> 
<td>" . str_replace(".",",",($arr[$a]['currency'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['rate'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_upnl_start'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_upnl_start'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_upnl_end'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_upnl_end'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_credit_start'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_credit_start'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_credit_end'])). "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_credit_end'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_profit'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_profit'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_swap'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_swap'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_cpnl'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_cpnl'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_commission'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_commission'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_adjustment'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_adjustment'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_nbr'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_nbr'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['nature_ifee'])) . "</td>
<td>" . str_replace(".",",",($arr[$a]['converted_ifee'])) . "</td>
<td>" . str_replace(".",",",($nature)) . "</td>
<td>" . str_replace(".",",",($usd)) . "</td>
</tr>";
}
echo "</table>";
/* -------------------------------------------------------------------------- */
//var_dump($sum_usd);
function str_contains($haystack, $needle) {
return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}