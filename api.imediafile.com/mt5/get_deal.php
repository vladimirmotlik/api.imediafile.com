<?php
if(!in_array($_SERVER['REMOTE_ADDR'], ["62.168.42.38", "95.217.41.149"])) die(json_encode(['status' => 'ERROR', 'message' => 'WHITELIST']));
echo 'dasd';
