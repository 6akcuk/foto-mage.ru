<?php

class APNSHelper {
  static function send($token, $msg) {
    Yii::import('application.vendors.*');
    require_once 'ApnsPHP/Log/Interface.php';
    require_once 'ApnsPHP/Log/Embedded.php';
    require_once 'ApnsPHP/Abstract.php';
    require_once 'ApnsPHP/Push.php';
    require_once 'ApnsPHP/Message.php';
    require_once 'ApnsPHP/Exception.php';

    $push = new ApnsPHP_Push(
      ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
      '/var/www/protected/apple_push_notification_development.pem'
    );
    $push->setRootCertificationAuthority('/var/www/protected/entrust_root_certification_authority.pem');
    $push->connect();

    // Instantiate a new Message with a single recipient
    $message = new ApnsPHP_Message($token);

    $message->setCustomIdentifier("Message");
    $message->setText($msg);
    $message->setSound();
    $message->setExpiry(30);
    $push->add($message);
    $push->send();

    $push->disconnect();
  }
}