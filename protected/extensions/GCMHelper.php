<?php
/**
 * Created by PhpStorm.
 * User: Sum
 * Date: 22.01.14
 * Time: 4:53
 */

class GCMHelper {
  static function send($registration_ids, $message) {
    if (!is_array($registration_ids)) $registration_ids = array($registration_ids);

    // Set POST variables
    $url = 'https://android.googleapis.com/gcm/send';

    $fields = array(
      'registration_ids' => $registration_ids,
      'data' => $message,
    );

    $headers = array(
      'Authorization: key=AIzaSyDCvVTIuXQtOPsWyVdmGIB7eHOS6uDpHXM',
      'Content-Type: application/json'
    );
    // Open connection
    $ch = curl_init();

    // Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Disabling SSL Certificate support temporarly
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

    // Execute post
    $result = curl_exec($ch);
    /*if ($result === FALSE) {
      die('Curl failed: ' . curl_error($ch));
    }*/

    // Close connection
    curl_close($ch);
    //echo $result;
  }
}