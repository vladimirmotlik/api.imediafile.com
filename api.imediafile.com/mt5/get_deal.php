<?php
if (!in_array($_SERVER['REMOTE_ADDR'], ["95.217.41.149", "62.168.42.38"])) die(json_encode(['status' => 'ERROR', 'message' => 'WHITELIST']));
/* ------------------------------------------------------------------------------------------------------------------ */
$to = (new DateTime("now", new DateTimeZone("Asia/Nicosia")))->format('Y-m-d H:i:s');
$from = ((new DateTime("now", new DateTimeZone("Asia/Nicosia")))->modify('-10 min'))->format('Y-m-d H:i:s');
/* ------------------------------------------------------------------------------------------------------------------ */
$mt5Connection = new mysqli("185.12.177.248", "itprg", '9`tksjg;JUt+$L*L', "mt5_live_report_server");
/* ------------------------------------------------------------------------------------------------------------------ */
$sql = "SELECT mt5_deals.Deal, `Email`, `mt5_deals`.`Login`, `MarketAsk`, `MarketBid`, `Symbol`, `Volume`, `Profit`, `ContractSize`, `Action`, `Reason`, `Time` FROM mt5_deals JOIN mt5_users ON mt5_deals.Login = mt5_users.Login WHERE Symbol != '' AND Entry = 1 AND Email != '' AND `Time` > '$from' AND `Time` < '$to '  ORDER BY Deal DESC";
$res = $mt5Connection->query($sql);
$mt5Entity = [];
while ($row = mysqli_fetch_assoc($res)) {
    $mt5Entity[$row['Deal']] = [
        'email' => $row['Email'], 'login' => $row['Login'], 'ask' => $row['MarketAsk'], 'bid' => $row['MarketBid'], 'login' => $row['Symbol'], 'volume' => $row['Volume'],
        'profit' => $row['Profit'], 'contract' => $row['ContractSize'], 'action' => $row['Action'], 'reason' => $row['Reason'], 'time' => $row['Time']
    ];
}
/* ------------------------------------------------------------------------------------------------------------------ */
if (count($mt5Entity) === 0) die(json_encode(['status' => 'ERROR', 'message' => 'NO_ROW']));
/* ------------------------------------------------------------------------------------------------------------------ */
die(json_encode($mt5Entity));
