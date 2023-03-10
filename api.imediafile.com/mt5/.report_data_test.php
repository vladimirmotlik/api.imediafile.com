<style>
table {border-collapse: collapse;}
td {border: 1px solid #ccc; padding: 5px;}
</style>
<?php
/* -------------------------------------------------------------------------- */
// GET HISTORY RATES TO ARRAY
//$mysqli2 = new mysqli('localhost', 'rates', 'Ka5aoslxe7jBjE7w', 'rates');
//$sql = "SELECT `date`, `rates` FROM `history_rates` WHERE `date` >= '2022-06-01' AND `date` <= '2022-06-30'";
//$result = $mysqli2->query($sql);
///$rates = array();
//while($row = mysqli_fetch_assoc($result)){
//$rates[$row['date']] = array('rates' => $row['rates']);
//}
/* -------------------------------------------------------------------------- */
// MT5M DATABASE CONNECTION
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$sql = "SELECT 
mt5_deals.Deal AS 'DEAL', 
mt5_deals.Login AS 'TPA', 
mt5_deals.Time AS 'DT',
mt5_deals.Profit AS 'PROFIT',
mt5_deals.Storage AS 'SWAP',
mt5_users.`Group` AS 'TRADING ACCOUNT_CURRENCY'
FROM mt5_deals 
JOIN mt5_symbols ON (mt5_deals.Symbol = mt5_symbols.Symbol)
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol != ''
AND mt5_users.`Group` NOT LIKE '%Test%'
AND mt5_users.`Group` LIKE '%3anglefx%'
AND mt5_deals.Time > '2022-06-01 00:00:00' 
AND mt5_deals.Time < '2022-06-30 23:59:59'
;";


$sql = "SELECT 
SUM(mt5_deals.Profit) AS 'PROFIT',
SUM(mt5_deals.Storage) AS 'SWAP',
mt5_users.`Group` AS 'TRADING ACCOUNT_CURRENCY'
FROM mt5_deals 
JOIN mt5_symbols ON (mt5_deals.Symbol = mt5_symbols.Symbol)
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol != ''
AND mt5_users.`Group` NOT LIKE '%Test%'
AND mt5_users.`Group` LIKE '%3anglefx%'
AND mt5_deals.Time > '2022-06-01 00:00:00' 
AND mt5_deals.Time < '2022-06-30 23:59:59'
GROUP BY mt5_users.`Group`
;";


$sql = "SELECT 
mt5_deals.Login AS 'TPA', 
SUM(mt5_deals.Profit) AS 'PROFIT',
SUM(mt5_deals.Storage) AS 'SWAP',
mt5_users.`Group` AS 'TRADING ACCOUNT_CURRENCY'
FROM mt5_deals 
JOIN mt5_symbols ON (mt5_deals.Symbol = mt5_symbols.Symbol)
JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol != ''
AND mt5_users.`Group` NOT LIKE '%Test%'
AND mt5_users.`Group` LIKE '%3anglefx%'
AND mt5_deals.Time > '2022-06-01 00:00:00' 
AND mt5_deals.Time < '2022-06-30 23:59:59'
GROUP BY mt5_deals.Login ORDER BY mt5_deals.Login
;";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
echo '<table><tr><td>TRADING ACCOUNT</td><td>CONVERSION RATE</td><td>NATURE CPNL</td><td>NATURE PROFIT</td><td>NATURE SWAP</td><td>CONVERTED CPNL (USD)</td><td>CONVERTED PROFIT (USD)</td><td>CONVERTED SWAP (USD)</td><td>ACCOUNT CURRENCY</td><td>TRADING GROUP</td></tr>';
$re = array('sum_usd' => 0, 'sum_eur' => 0, 'sum_czk' => 0, 'profit_usd' => 0, 'profit_eur' => 0, 'profit_czk' => 0, 'swap_usd' => 0, 'swap_eur' => 0, 'swap_czk' => 0);
$sum = 0;
$sum_usd = 0;
$profit_usd = 0;
$sum_eur = 0;
$profit_eur = 0;
$sum_czk = 0;
while($row = mysqli_fetch_assoc($result)){
/* -------------------------------------------------------------------------- */
// TRADING ACCOUNT CURRENCY
$accountCurrency = $row['TRADING ACCOUNT_CURRENCY'];
$currencyHelper = 'UNKNOWN';
if(str_contains($accountCurrency, 'USD')){
$currencyHelper = 'USD';
}
if(str_contains($accountCurrency, 'EUR')){
$currencyHelper = 'EUR';
}
if(str_contains($accountCurrency, 'CZK')){
$currencyHelper = 'CZK';
}
/* -------------------------------------------------------------------------- */
// CHECK IF DATE RATE EXIST
//if(!isset($rates[explode(' ', $row['DT'])[0]]['rates'])){
//die('misisn rate');
//}
//$dayRate = json_decode($rates[explode(' ', $row['DT'])[0]]['rates'], true);
/* -------------------------------------------------------------------------- */
if($currencyHelper == 'USD'){ 
$tpaRate = 1; 
$re['sum_usd'] += ($row['PROFIT'] + $row['SWAP']);
$re['profit_usd'] += ($row['PROFIT']);
$re['swap_usd'] += ($row['SWAP']);
}

if($currencyHelper == 'EUR'){ 
$tpaRate = 0.95444;
$re['sum_eur'] += ($row['PROFIT'] + $row['SWAP']);
$re['profit_eur'] += ($row['PROFIT']);
$re['swap_eur'] += ($row['SWAP']);
}

if($currencyHelper == 'CZK'){ 
$tpaRate = 23.613011;
$re['sum_czk'] += ($row['PROFIT'] + $row['SWAP']);
$re['profit_czk'] += ($row['PROFIT']);
$re['swap_czk'] += ($row['SWAP']);
}
/* -------------------------------------------------------------------------- */



echo "<tr>
<td>".$row['TPA']."</td>
<td>" . str_replace(".",",",$tpaRate) . "</td>
<td>".str_replace(".",",",($row['PROFIT'] + $row['SWAP']))."</td>
<td>".str_replace(".",",",$row['PROFIT'])."</td>
<td>".str_replace(".",",",$row['SWAP'])."</td>
<td>".str_replace(".",",",($row['PROFIT'] + $row['SWAP']) / $tpaRate)."</td>
<td>".str_replace(".",",",$row['PROFIT'] / $tpaRate)."</td>
<td>".str_replace(".",",",$row['SWAP'] / $tpaRate)."</td>
<td>$currencyHelper</td>
<td>$accountCurrency</td>
</tr>";

$sum += ($row['PROFIT'] + $row['SWAP']) / $tpaRate;
//$sum_usd += str_replace(".",",",$row['PROFIT'] / $tpaRate);


}
echo '</table>';

//var_dump($sum);


//var_dump($sum_usd);
function str_contains($haystack, $needle) {
return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}