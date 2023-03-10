<?php
header('Content-Type: text/html; charset=utf-8');
//+------------------------------------------------------------------+
//|                                                                  |
//+------------------------------------------------------------------+
class CMT5Request
{
  private $m_curl=null;
  private $m_server="demo.nelsonfx.com:443";
  //+----------------------------------------------------------------+
  //|                                                                |
  //+----------------------------------------------------------------+
  public function Init($server)
  {
    $this->Shutdown();
    if($server==null)
      return(false);
    $this->m_curl=curl_init();
    if($this->m_curl==null)
      return(false);
    //---
    curl_setopt($this->m_curl, CURLOPT_SSL_VERIFYPEER,FALSE);                        // comment out this line if you use self-signed certificates
    curl_setopt($this->m_curl, CURLOPT_MAXCONNECTS,1);                               // one connection is used
    curl_setopt($this->m_curl, CURLOPT_HTTPHEADER,array('Connection: Keep-Alive'));
    curl_setopt($this->m_curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    curl_setopt($this->m_curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    //---
    $this->m_server=$server;
    //---
    return(true);
  }
  //+----------------------------------------------------------------+
  //|                                                                |
  //+----------------------------------------------------------------+
  public function Shutdown()
  {
    if($this->m_curl!=null)
        curl_close($this->m_curl);
    $this->m_curl=null;
  }
  //+----------------------------------------------------------------+
  //|                                                                |
  //+----------------------------------------------------------------+
  public function Get($path)
  {
    if($this->m_curl==null)
      return(false);
    curl_setopt($this->m_curl,CURLOPT_POST,false);
    curl_setopt($this->m_curl,CURLOPT_URL,'https://'.$this->m_server.$path);
    curl_setopt($this->m_curl,CURLOPT_RETURNTRANSFER,true);
    $result=curl_exec($this->m_curl);
    if($result==false)
    {
      echo 'Curl GET error: '.curl_error($this->m_curl);
      return(false);
    }
    $code=curl_getinfo($this->m_curl,CURLINFO_HTTP_CODE);
    if($code!=200)
    {
      echo 'Curl GET code: '.$code;
      return(false);
    }
    return($result);
  }
  //+----------------------------------------------------------------+
  //|                                                                |
  //+----------------------------------------------------------------+
  public function Post($path, $body)
  {
    if($this->m_curl==null)
      return(false);
    curl_setopt($this->m_curl,CURLOPT_POST,true);
    curl_setopt($this->m_curl,CURLOPT_URL, 'https://'.$this->m_server.$path);
    curl_setopt($this->m_curl,CURLOPT_POSTFIELDS,$body);
    curl_setopt($this->m_curl,CURLOPT_RETURNTRANSFER,true);
    $result=curl_exec($this->m_curl);
    if($result==false)
    {
      echo 'Curl POST error: '.curl_error($this->m_curl);
      return(false);
    }
    $code=curl_getinfo($this->m_curl,CURLINFO_HTTP_CODE);
    if($code!=200)
    {
      echo 'Curl POST code: '.$code;
      return(false);
    }
    return($result);
  }
  //+----------------------------------------------------------------+
  //|                                                                |
  //+----------------------------------------------------------------+
  public function Auth($login, $password, $build, $agent)
  {
    if($this->m_curl==null)
      return(false);
    //--- send start
    $path='/auth_start?version='.$build.'&agent='.$agent.'&login='.$login.'&type=manager';
     // echo "$path<br/>";
    $result=$this->Get($path);
    if($result==false)
      return(false);
    $auth_start_answer=json_decode($result);
    if((int)$auth_start_answer->retcode!=0)
    {
      echo 'Auth start error : '.$auth_start_answer.retcode;
      return(false);
    }
    //--- Getting code from the hex string
    $srv_rand=hex2bin($auth_start_answer->srv_rand);
    //--- Hash for the response
    $password_hash=md5(mb_convert_encoding($password,'utf-16le','utf-8'),true).'WebAPI';
    $srv_rand_answer=md5(md5($password_hash,true).$srv_rand);
    //--- Random string for the MetaTrader 5 server
    $cli_rand_buf=random_bytes(16);
    $cli_rand=bin2hex($cli_rand_buf);
    //--- Sending the response
    $path='/auth_answer?srv_rand_answer='.$srv_rand_answer.'&cli_rand='.$cli_rand;
    //echo "$path<br/>";
    $result=$this->Get($path);
    if($result==false)
      return(false);
    $auth_answer_answer=json_decode($result);
    var_dump($auth_answer_answer);
    if((int)$auth_answer_answer->retcode!=0)
    {
      echo 'Auth answer error : '.$auth_answer_answer.retcode;
      return(false);
    }
    //--- Calculating a correct server response for the random client sequence
    $cli_rand_answer=md5(md5($password_hash,true).$cli_rand_buf);
    if($cli_rand_answer!=$auth_answer_answer->cli_rand_answer)
    {
      echo 'Auth answer error : invalid client answer';
      return(false);
    }
    //--- Everything is done
    return(true);
  }
}
//+----------------------------------------------------------------+
//| run                                                            |
//+----------------------------------------------------------------+
$request = new CMT5Request();
//---
if($request->Init('demo.nelsonfx.com:443') && $request->Auth(1011,"o7icgjgo",1011,"manager"))
{



//TRADE_BALANCE|LOGIN=xxxx|TYPE=y|BALANCE=zzzz|COMMENT=aaaa|CHECK_MARGIN=1\r\n
//$result=$request->Get('/trade_balance?login=1000700&balance=-100&type=2&comment=testDepo&check_margin=1'); //balance

//$result=$request->Get('/user_add?group=demo\Triangleview\3anglefx%20Real%201\CZK_30&agent=0&login=1000700&status=test&comment=test&leverage=33&rights=289&name=Test%20Testovic&company=pineal&language=cz&city=prague&state=CzechRepublic&zipcode=10100&email=test@test.cz&pass_phone=1234abcD&pass_main=1234abcD&pass_investor=1234abcD&address=Uzbecka%201&phone=420773795853'); 



//$result=$request->Get('/user_pass_change?login=1000622&password=Dcba1234&type=main');

// update user
//$result=$request->Get('/user_update?login=1000700&rights=289&name=' . rawurlencode('Příliš Žluťoučký') . '&company=pineal&language=cz&city=prague&state=CzechRepublic&zipcode=10100&email=test@test.cz'); 
//echo $result . '<br/><br/>';

//USER_UPDATE|LOGIN=xxxx|RIGHTS=|GROUP=xxxx|NAME=xxxx|
//COMPANY=|LANGUAGE=|CITY=|STATE=|ZIPCODE=|ADDRESS=|PHONE=|EMAIL=| 
//ID=|STATUS=|COMMENT=|COLOR=|PASS_PHONE=|LEVERAGE=|AGENT=|\r\n 
//HISTORY_GET_TOTAL|LOGIN=login|FROM=date|TO=date|\r\n



//$result=$request->Get('/history_get_total?login=1000700&offset=0&total=1000'); // obchody celkem
//echo "<pre>$result</pre>";

//$result=$request->Get('/history_get_page?login=1000700&offset=0&total=1000'); // obchody celkem
//$result=$request->Get('/order_get_total?login=1000700'); // obchody celkem
//$result=$request->Get('/history_get_total?login=1000700'); // obchody celkem
//$result=$request->Get('/deal_get_page?login=1000700&total=100&offset=0'); // deals
//$result=$request->Get('/position_get_page?login=1000700&total=100&offset=0'); // otevrene pozice
//$result=$request->Get('/position_get_total?login=1000700');  // pocet traders open
//$result=$request->Get('/user_account_get?login=1000700');  // info a aktualni situaci, margin, profit atd. 
//$result=$request->Get('/user_get?login=1000700'); 
//$result=$request->Get('/user_logins?group=demo*'); // vrati loginy 43 uzivatelu "1000540", "1000562", "1000571", "1000587", "1000590", "1000593", "1000597", 
//"1000598", "1000599", "1000607", "1000610", "1000611", "1000612", "1000613", "1000614", "1000615", "1000616", "1000617", "1000618", "1000619", "1000620", "1000621", 
//"1000622", "1000623", "1000624", "1000625", "1000626", "1000627", "1000629", "1000631", "1000632", "1000588", "1000592", "1000600", "1000601", "1000604", 
//"1000605", "1000628", "1000589", "1000595", "1000596", "1000609", "1000630"] } 
//$result=$request->Get('/group_next?index=0'); //"demo\\Triangleview\\3anglefx Real 1\\CZK_30"
//$result=$request->Get('/group_next?index=1'); //"demo\\Triangleview\\3anglefx Real 1\\EUR_30"
$result=$request->Get('/group_next?index=2'); //"demo\\Triangleview\\3anglefx Real 1\\USD_30"
//$result=$request->Get('/symbol_list'); //3
  //$result=$request->Get('/group_total'); //3
  //$result=$request->Get('/user_total'); //43
  //---
  if($result!=false)
  {
    echo "<pre>$result</pre>";
  }
}
$request->Shutdown();
?>
