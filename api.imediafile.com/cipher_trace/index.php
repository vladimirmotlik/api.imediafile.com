<?php
/* -------------------------------------------------------------------------- */
saveInputData("CALLBACK DATA 1", $_REQUEST, null);
saveInputData("CALLBACK DATA 2", $_POST, null);
saveInputData("CALLBACK DATA 3", $_GET, null);
saveInputData("CALLBACK DATA 4", $_SERVER, null);

saveInputData("CALLBACK DATA 5", json_decode($_REQUEST, true), null);
saveInputData("CALLBACK DATA 6", json_decode($_POST, true), null);
saveInputData("CALLBACK DATA 7", json_decode($_GET, true), null);


/* -------------------------------------------------------------------------- */
function saveInputData($msg, $v1, $v2) {
    $f = fopen(".DATA", "a");
    $date = date("Y-m-d H:i:s");
    fwrite($f, " -----\n");
    fwrite($f, " $date \n");
    fwrite($f, $_SERVER['REMOTE_ADDR'] . "\n");
    fwrite($f, " -----\n");
    fwrite($f, " $msg\n");
    fwrite($f, " -----\n");
    foreach ($v1 as $key => $value) {
        fwrite($f, "\n $key = '$value' ");
    }
    fwrite($f, "\n-----\n");
    foreach ($v2 as $key => $value) {
        fwrite($f, "\n $key = '$value' ");
    }
    fwrite($f, "\n-----\n\n\n");
    fclose($f);
}

/* -------------------------------------------------------------------------- */
?>