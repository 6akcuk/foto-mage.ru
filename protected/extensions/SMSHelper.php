<?php

class SMSHelper {
  static public function send($phone, $text) {
    if (strlen($text) == 0) return;

    $text = urlencode($text);
    $phone = urlencode($phone);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt($curl, CURLOPT_URL, "http://my.sms10.su/sendsms.php?user=anonimous&pwd=213321&sadr=89008003000&dadr=$phone&text=$text");
    //curl_setopt($curl, CURLOPT_POST, true);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, $text);
    $out = curl_exec($curl);
    curl_close($curl);

    return $out;
  }
}