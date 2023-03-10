<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="cs" lang="cs">
<head><meta http-equiv="content-type" content="text/html;charset=UTF-8" /><title>LANDING</title>
<script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css"/> 
<link href="https://fonts.googleapis.com/css?family=Raleway" rel="stylesheet"> 
<script src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"> </script>
<style>
* {font-family: 'Raleway', sans-serif;} 
td { text-align: center; font-size: 11px; padding: 0px 0px; color: #111; border-right: 1px solid #ccc; }
tr:nth-child(2n){background-color: #eee;}
h3, a {text-decoration: none; color: #000; text-align: center;}
#div { width: 50%; margin: 0 auto; border: 1px solid #ccc; padding: 20px; background-color: #fcfbfb;}
thead {background-color: #c0dbff;}
</style>
</head>
<body>

<script>
$(document).ready(function() {
    $('#example').DataTable();
} );
</script>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$server = "localhost";
$uid = "landing";
$dtb = "landing";
$pwd = "LMhw0rynhiS5j4Zo";
/* -------------------------------------------------------------------------- */
$c = new mysqli($server, $uid, $pwd, $dtb);
/* -------------------------------------------------------------------------- */
$f = scandir("/var/www/api.imediafile.com/landing/");
/* -------------------------------------------------------------------------- */
foreach ($f as $v) {
if (strpos($v, '.html') !== false) {
$r = $c->query("SELECT `id`, `address`, `brand`, `dt`, `token`, `is_approved` FROM `sites` WHERE `address` = '$v' AND is_approved = 0;");
if($r->num_rows === 0){
$g = generate(16);
$r = $c->query("INSERT INTO `sites` (`address`, `token`) VALUES ('$v','$g');");
}
}
}
/* -------------------------------------------------------------------------- */
$res = "<div id='div'><strong>To Approve:</strong><br/><br/><table id='example' class='display' width='100%'><thead><tr><td>ID</td><td>ADDRESS</td><td>DT</td><td>LINK</td><td>ACTION</td></tr></thead><tbody>";
$r = $c->query("SELECT `id`, `address`, `brand`, `dt`, `token`, `is_approved` FROM `sites` WHERE is_approved = 0;");
while ($rr = $r->fetch_assoc()) {
$is = false;
foreach ($f as $v) {
if($rr['address'] == $v){
$is = true;
break;
}
}
if(!$is){
$c->query("DELETE FROM `sites` WHERE `id` = " . $rr['id']);
}

if($is){
$res .= "<tr><td>" . $rr['id'] . "</td><td>" . $rr['address'] . "</td><td>" . $rr['dt'] . "</td><td><a href='http://landing.imediafile.com/" . $rr['address'] ."' target='_blank'>http://landing.imediafile.com/" . $rr['address'] . "</a></td><td><a href='?id=".$rr['id']."&action=approve'>APPROVE</a></td></tr>";
}
}
$res .= "</tbody></table></div>";
echo $res;
/* -------------------------------------------------------------------------- */
$r->free();
$c->close();
/* -------------------------------------------------------------------------- */
$e = scandir("/var/www/api.imediafile.com/");
foreach ($e as $d){
if(strpos($d, 'land.') !== false){
echo $d . "<br/>";
}
}


function generate($length){
$a = array('1','2','3','4','5','6','7','8','9','0','a','b','c','d','e','f','g','h','j','k','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','J','K','L','M','N','O','P','R','Q','S','T','U','V','W','Y','Z','X');
$p = '';
for($i = 0; $i < $length; $i++){
$p .= $a[rand(0,count($a) - 1)];
}
return $p;
}
?>

<div id="dialog">sadsadasdada</div>
</body>
</html>