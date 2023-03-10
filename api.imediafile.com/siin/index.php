<?php
savePostData($_SERVER);
savePostData($_POST);
savePostData($_GET);
savePostData($_REQUEST);
function savePostData($vars) {
    $f = fopen(".POST.txt", "a");
    $date = date("Y-m-d H:i:s");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, " $date\n");
    fwrite($f, "\n --------------------------------------------------------------\n");
    fwrite($f, " ------------------------ HEADER ---------------------------------\n");
    fwrite($f, " --------------------------------------------------------------\n");
    foreach ($vars as $key => $value) {
        fwrite($f, "\n $key = '$value' ");
    }
    fwrite($f, "\n --------------------------------------------------------------\n\n\n");
    fclose($f);
}