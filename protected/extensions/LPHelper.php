<?php

class LPHelper {
  static public function send($channel, $text) {
    if (strlen($text) == 0) return;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt($curl, CURLOPT_URL, 'http://queue.e-bash.me/pubch?id='. $channel);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $text);
    curl_exec($curl);
    curl_close($curl);
  }
}