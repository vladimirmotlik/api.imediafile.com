<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');
/* -------------------------------------------------------------------------- */
$crmList = array(
"B_CRM_FGMARKETS|fgmarkets.com", "B_CRM_GLOBTREX|globtrex.asia", "B_CRM_DUKASPRO|dukaspro.com", "B_CRM_IMTTRADE|imttrade.com", "B_CRM_NELSONFX|nelsonfx.com", "B_CRM_VELMARKET|velmarket.com", "CRM_5FX|5fx.co.uk", "CRM_8888FX|8888fx.co.uk",
"CRM_HOUSECAPITAL|housecapital.co.uk", "CRM_EASYFOREX|easyforex.pro", "CRM_DERIMARKETS|derimarkets.com", "CRM_BANKERSMARKETS|bankersmarkets.co.uk", "CRM_CFDINTERBANK|cfdinterbank.com", "CRM_DIDYFX|didyfx.com", "CRM_DOUBLECAPITAL|double.capital",
"CRM_BROKERJET|brokerjet.live", "CRM_DOUBLETRADE|double.trade", "CRM_CTTRADES|cttrades.com", "CRM_24HTRADING|24htrading.co.uk", "CRM_PRIMEMARKETS|primemarkets.net", "CRM_DEPPCAPITAL|deppcapital.co.uk", "CRM_KEDARFX|kedarfx.com", 
"CRM_NOORTRADES|noortrades.com", "CRM_ROSYFX|rosyfx.com", "CRM_IPROMARKETS|ipromarkets.com", "CRM_COMNETCAPITAL|comnet.capital", "CRM_VIMAFX|vimafx.com", "CRM_TELECAPITAL|telecapital.co.uk", "CRM_BROKEROX|brokerox.com", "CRM_JTBROKER|jt-broker.com",
"CRM_BIDASKBIT|bidaskbit.com", 'CRM_ZETATRADERS|zetatraders.com', 'CRM_FANNEXX|fannexx.com'
);
/* -------------------------------------------------------------------------- */
if(isset($_GET['poolCRM'])){
if($_GET['poolCRM'] === "internalcrm"){
echo file_get_contents("http://crm.pineal.eu/pools.php");
}
/* -------------------------------------------------------------------------- */
else if($_GET['poolCRM'] === "alphacrm"){
$ch1 = curl_init("https://client.3anglefx.com/api.lead/promo-code");
curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch1, CURLOPT_CUSTOMREQUEST, 'GET');
curl_setopt($ch1, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch1);
$json = json_decode($response, true);
foreach($json as $k => $v){
$arr[] = array('Id' => $k, 'Name' => $v['domain']);
}
curl_close($ch1);
die(json_encode($arr));
}
/* -------------------------------------------------------------------------- */
else if($_GET['poolCRM'] === "leaddeskeu"){
$re = file_get_contents("http://api.leaddesk.com/?auth=20b661c03d0b67ea15559744e8f197e8&mod=calling_list&cmd=list");
$r = json_decode($re, true);
for($i = 0; $i < count($r); $i++){
$result[] = array("Id" => $r[$i]["id"], "Name" => $r[$i]["name"]);
}
echo json_encode($result, true);
}
/* -------------------------------------------------------------------------- */
else if($_GET['poolCRM'] === "leaddesk"){
$re = file_get_contents("http://api.leaddesk.com/?auth=b455e4ca59ed06e35c10f7d70b968974&mod=calling_list&cmd=list");
$r = json_decode($re, true);
for($i = 0; $i < count($r); $i++){
$result[] = array("Id" => $r[$i]["id"], "Name" => $r[$i]["name"]);
}
echo json_encode($result, true);
}
/* -------------------------------------------------------------------------- */
else if($_GET['poolCRM'] === "leverate"){
$arr = array(
    array("Name" => "Russia Prague 2", "Id" =>"9dff9fcc-6a74-e811-80d3-005056b11811"),
    array("Name" => "CoverdealFX", "Id" =>"5622f130-bc9b-e811-80d3-005056b11811"),
    array("Name" => "Lebanon", "Id" =>"d73defc2-b1c0-e811-80d3-005056b11811"),
    array("Name" => "Beirut", "Id" =>"0e47174a-b2c0-e811-80d3-005056b11811"),
    array("Name" => "Retention Mnuk", "Id" =>"b826fa68-c7e1-e811-80d3-005056b11811"),
    array("Name" => "Cape Town", "Id" =>"526b622a-1951-e811-80ce-005056b12a8f"),
    array("Name" => "Cape Town Team 1", "Id" =>"79bf82d8-2251-e811-80ce-005056b12a8f"),
    array("Name" => "NelsonFX", "Id" =>"2037831d-2351-e811-80ce-005056b12a8f"),
    array("Name" => "South Africa", "Id" =>"6a3cdb48-2351-e811-80ce-005056b12a8f"),
    array("Name" => "Mardorm", "Id" =>"021fb29e-b52a-e811-80d1-005056b12b5d"),
    array("Name" => "FG Markets", "Id" =>"a83037c2-b52a-e811-80d1-005056b12b5d"),
    array("Name" => "Czech Republic", "Id" =>"fc871ce5-b52a-e811-80d1-005056b12b5d"),
    array("Name" => "Prague", "Id" =>"063e625c-b62a-e811-80d1-005056b12b5d"),
    array("Name" => "Russia Prague 1", "Id" =>"ccc48fb1-b62a-e811-80d1-005056b12b5d"),
    array("Name" => "Cyprus", "Id" =>"633fadd0-b62a-e811-80d1-005056b12b5d"),
    array("Name" => "Limassol", "Id" =>"3a6411e1-b62a-e811-80d1-005056b12b5d"),
    array("Name" => "Limassol Team 1", "Id" =>"cf2060f9-b62a-e811-80d1-005056b12b5d"),
    array("Name" => "India", "Id" =>"abe1bca5-ded5-e811-80d5-005056b13e21"),
    array("Name" => "Mumbai Team 1", "Id" =>"1edb15b8-ded5-e811-80d5-005056b13e21"),
    array("Name" => "Mumbai Team 2", "Id" =>"2f3a5fbe-ded5-e811-80d5-005056b13e21"),
    array("Name" => "Retention SA", "Id" =>"e641c061-06e4-e811-80d5-005056b13e21"),
    array("Name" => "Dubai", "Id" =>"c3a43b5a-d8eb-e811-80d5-005056b13e21"),
    array("Name" => "Dubai India team 1", "Id" =>"57a2a094-d8eb-e811-80d5-005056b13e21"),
    array("Name" => "Albania", "Id" =>"1fea9f03-2f1a-e911-80ce-005056b12a8f"),
    array("Name" => "Tirana Team 1", "Id" =>"b990e862-2f1a-e911-80ce-005056b12a8f"),
    array("Name" => "555 markets", "Id" =>"319e70e0-8203-e911-80d5-005056b13e21"),
    array("Name" => "555 Spanish", "Id" =>"86e1487a-b623-e911-80ce-005056b12a8f"),
    array("Name" => "555 English", "Id" =>"73356ba9-b623-e911-80ce-005056b12a8f"),
    array("Name" => "Valencia", "Id" =>"5bfa32d0-b623-e911-80ce-005056b12a8f"),
    array("Name" => "Retention Mnuk", "Id" =>"b826fa68-c7e1-e811-80d3-005056b11811"),
    array("Name" => "Retention SA", "Id" =>"e641c061-06e4-e811-80d5-005056b13e21"),
    array("Name" => "Algeria FGM", "Id" =>"283cbd1f-055d-e911-80d2-005056b12b5d"),
    array("Name" => "Latam Barcelona", "Id" =>"53dea6ca-8a5f-e911-80d6-005056b13e21"),
    array("Name" => "Vietnam", "Id" =>"a10dd46a-8254-e911-80d5-005056b11811"),
    array("Name" => "555 Franke Team 1", "Id" =>"5580c648-62a1-e911-80d5-005056b11811"),
    array("Name" => "555 Moti", "Id" =>"63baedfb-94a8-e911-80d6-005056b13e21"),
    array("Name" => "Brazil Team 1", "Id" =>"0158a313-9a2e-e911-80d2-005056b12b5d")
);
ksort($arr);
echo json_encode($arr, true);
}
/* -------------------------------------------------------------------------- */
else if($_GET['poolCRM'] === "coverdealfx.com"){
$arr = array(
array("Name" => "Coverdeal", "Id" => "93207151-4c14-ea11-a2ce-005056b1b8b7"),
array("Name" => "Coverdeal EN", "Id" => "a2cc2f92-5331-ea11-a2cc-005056b10e15"),
array("Name" => "Coverdeal ES", "Id" => "689777c2-5331-ea11-a2cc-005056b10e15"),
array("Name" => "Czech Republic", "Id" => "da10b3c4-1c18-ea11-a2cc-005056b10e15"),
array("Name" => "CZK Team 1", "Id" => "fb075de7-1c18-ea11-a2cc-005056b10e15"),
array("Name" => "CZK Team 2", "Id" => "004c2afd-1c18-ea11-a2cc-005056b10e15"),
array("Name" => "CZK Team 3", "Id" => "0ac7310a-1d18-ea11-a2cc-005056b10e15"),
array("Name" => "CZK Team 3", "Id" => "4685ed0f-831d-ea11-a2cc-005056b10e15"),
array("Name" => "CZK Team 3", "Id" => "e922cb42-831d-ea11-a2cc-005056b10e15"),
array("Name" => "CZK Team 4", "Id" => "5ffd5c17-1d18-ea11-a2cc-005056b10e15"),
array("Name" => "CZK Team 5", "Id" => "169e791f-1d18-ea11-a2cc-005056b10e15"),
array("Name" => "CZK Team 6", "Id" => "59932ebe-704d-ea11-a2cf-005056b1b8b7"),
array("Name" => "CZK Team 7", "Id" => "e4a6f3a4-ab57-ea11-a2cf-005056b13240"),
array("Name" => "CZK Team 8", "Id" => "1ed22d89-3d59-ea11-a2cf-005056b13240"),
array("Name" => "CZK Team 9", "Id" => "72570211-375d-ea11-a2cf-005056b13240"),
array("Name" => "CZK Team 10", "Id" => "31f48ba0-375d-ea11-a2cf-005056b13240"),
array("Name" => "CZK Team 11", "Id" => "fb972602-385d-ea11-a2cf-005056b13240"),
array("Name" => "CZK Team 12", "Id" => "8261ee43-385d-ea11-a2cf-005056b13240"),
array("Name" => "CZK Team 13", "Id" => "d77975f6-1d5e-ea11-a2d0-005056b1e92b"),
array("Name" => "CZK Team 14", "Id" => "09def74c-1e5e-ea11-a2d0-005056b1e92b"),
array("Name" => "CZK Team 15", "Id" => "eadc92a5-1e5e-ea11-a2d0-005056b1e92b"),
array("Name" => "CZK Team 16", "Id" => "25cfb70d-275e-ea11-a2ce-005056b10e15"),
array("Name" => "CZK Team 17", "Id" => "2a385830-dd5e-ea11-a2cf-005056b13240"),
array("Name" => "CZK Team 18", "Id" => "860920bc-c569-ea11-a2cf-005056b1b8b7"),
array("Name" => "CZK Team 19", "Id" => "937ae64c-1874-ea11-a2cf-005056b1b8b7"),
array("Name" => "ES Team 1", "Id" => "b22392fe-605d-ea11-a2d0-005056b1e92b")
);
ksort($arr);
echo json_encode($arr, true);
}
/* -------------------------------------------------------------------------- */
else{
$request = strtolower(trim($_GET['poolCRM']));
$is_exist = false;
$row = array();
foreach($crmList as $list){
$row = explode("|", $list);  //db name and link
if($row[1] == $request){
$is_exist = true;
break;
}
}
if($is_exist){
$db_connection = pg_connect("host=178.162.205.229 port=5432 dbname=". strtolower($row[0]) ." user=imt_ro password=Kalia-Bernie");
$result = pg_query($db_connection, 'SELECT "PoolReferrers"."ReferrerId" AS "Id", "Pools"."Name" AS "Name" FROM "Pools" JOIN "PoolReferrers" ON "Pools"."Id" = "PoolReferrers"."PoolId" ORDER BY "Pools"."Name";');
echo json_encode(pg_fetch_all($result), true);
}else{
echo json_encode(array("error" => "Parameter poolCRM contains no valid data"));
}
}
/* -------------------------------------------------------------------------- */
}else{
$res = array("leverate", "coverdealfx.com", "leaddeskeu", "leaddesk", "internalcrm", "alphacrm");
foreach($crmList as $list){
$row = explode("|", $list);  //db name and link
$res[] = $row[1];
}
echo json_encode($res, true);
}
/* -------------------------------------------------------------------------- */
?>