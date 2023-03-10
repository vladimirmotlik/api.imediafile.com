<?php

savePostData();

function saveInputData($v2) {
$f = fopen(".DATA", "a");
$date = date("Y-m-d H:i:s");
fwrite($f, " ---------- " . $date . " ---------- ");
foreach ($v2 as $key => $value) {
@fwrite($f, "\n $key = '$value' ");
}
fwrite($f, "\n -----------------------------------------\n\n");
fclose($f);
}



function savePostData() {
    $f = fopen(".POST.txt", "a");
    $date = date("Y-m-d H:i:s");
    fwrite($f, " --------------------------------------------------------------\n");
    fwrite($f, " $date\n");
    fwrite($f, "\n --------------------------------------------------------------\n");
    $obj1 = file_get_contents("php://input");
    $json = json_decode($obj1, true);
    /* ^^ */
    saveInputData($json);
    /* ^^ */
    $TransactionId = $json['TransactionId'];
    $UserId = $json['UserId'];
    $LeadId = $json['LeadId'];
    $DomainName = strtolower($json['DomainName']);
    $ClientReferrer = $json['ClientReferrer'];
    $Email = $json['Email'];
    $ClientName = $json['ClientName'];
    $FundProcessor = $json['FundProcessor'];
    $PaymentGateway = $json['PaymentGateway'];
    $Amount = $json['Amount'];
    $CurrencyCode = $json['CurrencyCode'];
    $StatusId = $json['StatusId'];
    $CreatedDate = $json['CreatedDate'];
    $Reason = $json['Reason'];
    $PoolName = urlencode($json['PoolName']);
    $Ftd = $json['Ftd'];
    $SyncWithTradingPlatform = $json['SyncWithTradingPlatform'];
    $PhoneNumber = str_replace("+", "", $json['PhoneNumber']);
    $ConversionRate = $json['ConversionRate'];
    $AmountUSD = $json['AmountUSD'];
    
    
    if($FundProcessor === 'Bonus'){
        fwrite($f, "bonus, thread canceled");
        fwrite($f, "\n --------------------------------------------------------------\n");
       return;
    }
    
    if($FundProcessor === 'AccountAdjustment'){
        fwrite($f, "AccountAdjustment, thread canceled");
        fwrite($f, "\n --------------------------------------------------------------\n");
       return;
    }
    
    if($FundProcessor === 'Account Reconcile'){
        fwrite($f, "Account Reconcile, thread canceled");
        fwrite($f, "\n --------------------------------------------------------------\n");
       return;
    }
    
    if($FundProcessor === 'InternalTransfer'){
        fwrite($f, "InternalTransfer, thread canceled");
        fwrite($f, "\n --------------------------------------------------------------\n");
       return;
    }
    
    if($StatusId == 4){
        fwrite($f, "Rejected PP, thread canceled");
        fwrite($f, "\n --------------------------------------------------------------\n");
       return;
    }

    $result = "blank";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_URL, "https://crm.coverdeal.eu:8999/_CRM_DATA/API_NEW?action=robot&domainName=$DomainName&transactionId=$TransactionId&poolName=$PoolName");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    curl_close($ch);


    $final = "Unknown error";
    $arr = explode("^", $result);
    if (count($arr) !== 2) {
        $final = "Error with explode";
    } else {
        if ($arr[0] == 'error') {
            $final = "Error - " . $arr[1];
        } else {
            $arr1 = explode("|", $arr[1]);
            $is_first = $arr1[0];
            $aid = $arr1[1];
            $poolId = $arr1[2];

            $c = new mysqli('crm.coverdeal.eu', 'external1', '5HLqsVHHhp4dYPhL', 'pineal');

            if ($Amount > 0) {
            
            $isCrypto = 0;
            
            if($CurrencyCode === 'ETH'){
            $Amount = round($Amount * 100000000, 0); 
            $isCrypto = 1;         
            }
            if($CurrencyCode === 'BTC'){
            $Amount = round($Amount * 100000000, 0);
            $isCrypto = 1;            
            }
            if($CurrencyCode === 'BCH'){
            $Amount = round($Amount * 100000000, 0);  
            $isCrypto = 1;          
            }
            if($CurrencyCode === 'LTC'){
            $Amount = round($Amount * 100000000, 0); 
            $isCrypto = 1;           
            }
            

            
            // oprava predelano is_first na Ftd - firstDeposit problem
                $closeQuery = "INSERT INTO _infopanel_agenda_c (dt,name,aid,send_money,currency,deposit,is_first,salesman,retention,manager,id_pool,author,telephone,email,is_campaign, is_crypto) VALUES 
                ('$CreatedDate','$ClientName',$aid,$Amount,'$CurrencyCode',$AmountUSD,$Ftd,'XXX','XXX','XXX',$poolId,'ROBOTv2','00$PhoneNumber','$Email', 0, $isCrypto);";
                if ($c->query($closeQuery) === TRUE) {
                  fwrite($f, "\n --------------------------------------------------------------\n");
                  fwrite($f, "OK - Saved to database");
                  fwrite($f, "\n --------------------------------------------------------------\n");
                } else {
                  fwrite($f, "\n --------------------------------------------------------------\n");
                  fwrite($f, "Error: " . $closeQuery . " " . $c->error);
                  fwrite($f, "\n --------------------------------------------------------------\n");
                }
            } else {            
                $AmountUSD = $AmountUSD * -1;
                $withdrawalQuery = "INSERT INTO _infopanel_agenda_w(dt,name,aid,withdraw,retention,manager,id_pool,author,telephone,email) VALUES 
                ('$CreatedDate','$ClientName',$aid,$AmountUSD,'XXX','XXX',$poolId,'ROBOTv2','00$PhoneNumber','$Email');";
                if ($c->query($withdrawalQuery) === TRUE) {
                  fwrite($f, "\n --------------------------------------------------------------\n");
                  fwrite($f, "OK - Saved to database");
                  fwrite($f, "\n --------------------------------------------------------------\n");
                } else {
                  fwrite($f, "\n --------------------------------------------------------------\n");
                  fwrite($f, "Error: " . $withdrawalQuery . " " . $c->error);
                  fwrite($f, "\n --------------------------------------------------------------\n");
                }
            }          
            $c->close();
            $final = "Success - AID: $aid, is first: $is_first - oprava - $Ftd, poolId: $poolId currencyCode: $CurrencyCode ";
        }
    }


    fwrite($f, $obj1);
    fwrite($f, "\n --------------------------------------------------------------\n");
    fwrite($f, $final);
    fwrite($f, "\n --------------------------------------------------------------\n");
    fclose($f);
}
