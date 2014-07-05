<?php

class VKHelper {
  private $_access_token;

  public function __construct($access_token) {
    $this->_access_token = $access_token;
  }

  public function method($method, $params = array()) {
    $query = '/method/'. $method .'?'. http_build_query($params) .'&v=5.17&access_token='. $this->_access_token;
    $sig = md5($query . Yii::app()->params['vk_client_secret']);

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt($curl, CURLOPT_URL, 'https://api.vk.com'. $query .'&sig='. $sig);
    //curl_setopt($curl, CURLOPT_POST, true);
    //curl_setopt($curl, CURLOPT_POSTFIELDS, $text);
    $data = curl_exec($curl);
    curl_close($curl);

    return json_decode($data, true);
  }

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