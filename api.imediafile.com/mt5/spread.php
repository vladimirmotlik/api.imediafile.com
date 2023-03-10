<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300&display=swap" rel="stylesheet">
<style>
table {border-collapse: collapse;}
td {border: 1px solid #ccc; padding: 5px; text-align: center;}
tr:nth-child(even) {background-color: #f2f2f2;}
.header {background-color: #333; color: white;}
* {font-family: 'Hind Siliguri', sans-serif; font-size: 13px;}
</style>
<?php
if(!isset($_REQUEST['from']) || !isset($_REQUEST['to']) || !isset($_REQUEST['account'])) die('param');
/* -------------------------------------------------------------------------- */
$mysqli2 = new mysqli('localhost', 'rates', 'Ka5aoslxe7jBjE7w', 'rates');
$sql = "SELECT `date`, `rates` FROM `history_rates` WHERE `date` >= '" . $dateArray['fromDate'] . "'";
$result = $mysqli2->query($sql);
$rates = array();
while($row = mysqli_fetch_assoc($result)){
$rates[$row['date']] = array('rates' => $row['rates']);
}
/* -------------------------------------------------------------------------- */
$mysqli = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* -------------------------------------------------------------------------- */
$sql = "SELECT 
mt5_deals.Deal AS ID,
mt5_deals.MarketAsk AS ASK,
mt5_deals.MarketBid AS BID,
mt5_deals.ContractSize AS SIZE,
mt5_deals.Volume AS VOLUME,
mt5_deals.Symbol AS SYMBOL,
mt5_symbols.Path AS PATH,
mt5_symbols.CurrencyProfit AS CP
FROM mt5_deals JOIN mt5_symbols ON (mt5_deals.Symbol = mt5_symbols.Symbol) JOIN mt5_users ON (mt5_deals.Login = mt5_users.Login)
WHERE mt5_deals.Symbol != '' AND mt5_deals.Time > '" . $_REQUEST['from'] . " 00:00:00' AND mt5_deals.Time < '" . $_REQUEST['to'] . " 23:59:59' AND mt5_deals.Login IN ('".$_REQUEST['account']."');";
/* -------------------------------------------------------------------------- */
$result = $mysqli->query($sql);
/* -------------------------------------------------------------------------- */
echo '<div id="sum"></div><table><tr>';
echo "<td>ID</td>"; //0
echo "<td>ASK</td>"; //1
echo "<td>BID</td>"; //2
echo "<td>ASK - BID</td>"; //3
echo "<td>PATH</td>"; //4
echo "<td>CONTRACT SIZE</td>"; //5
echo "<td>SYMBOL</td>"; //6
echo "<td>IS_VOLUME_DIVIDED</td>"; //7
echo "<td>VOLUME</td>"; //8
echo "<td>SPREAD</td>"; //9
echo "<td>CURRENCY</td>"; //10
echo "<td>USD SPREAD</td>"; //11
echo "<td>RATE</td>"; //12
echo '</tr>'; 

$sum = 0;

while($row = mysqli_fetch_assoc($result)){



$id = $row['ID'];
$ask = $row['ASK'];
$bid = $row['BID'];
$dif = $ask - $bid;
$size = $row['SIZE'];
$path = $row['PATH'];
$vol = $row['VOLUME'];
$sym = $row['SYMBOL'];
$cur = $row['CP'];



// CANCEL CHANGES
str_contains(strtoupper($path), 'FOREX') ? $corr = 1 : $corr = 1;
$corr === 1 ? $vol /= 10000 : $vol = $vol;
$spread = round(($dif * $size * $vol) / 2, 2);

$rate = json_decode($rates['2022-11-30']['rates'], true);
if (json_last_error() !== JSON_ERROR_NONE) die('MISISNG_RATE');
isset($rate['USD' . strtoupper($cur)]) ?: die('MISISNG_RATE');

$usd = round($spread / $rate['USD' . strtoupper($cur)], 2);


echo '<tr>'; 
echo "<td>$id</td>"; //1
echo "<td>$ask</td>"; //1
echo "<td>$bid</td>"; //2
echo "<td>$dif</td>"; //3
echo "<td>$path</td>"; //4
echo "<td>$size</td>"; //5
echo "<td>$sym</td>"; //6
echo "<td>$corr</td>"; //7
echo "<td>$vol</td>"; //8
echo "<td style='background-color: #cfc'>$spread</td>"; //9
echo "<td>$cur</td>"; //10
echo "<td>$usd</td>"; //11
echo "<td>".$rate['USD' . strtoupper($cur)]."</td>"; //12

$sum += $usd;


echo '</tr>'; 


}
echo "</table>"; 
echo '<script>document.getElementById("sum").innerHTML = "<h1>ACCOUNT : ' . $_GET["account"] . ' | DATES : ' . $_GET["from"] . ' - ' . $_GET['to'] .  ' | SPREAD : '.$sum.' USD | FORMULA : ((ASK - BID) * (VOLUME / 10000) * CONTRACT_SIZE) / 2</h1>";</script>';



function str_contains($haystack, $needle)
{
    return $needle !== '' && mb_strpos($haystack, $needle) !== false;
}
