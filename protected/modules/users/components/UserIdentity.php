<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 25.09.12
 * Time: 14:41
 * To change this template use File | Settings | File Templates.
 */

class UserIdentity extends CBaseUserIdentity {
  public $id;
  public $email;
  public $password;
  public $hash;
  public $name;

  public function __construct($email, $password) {
      $this->email = $email;
      $this->password = $password;
  }

  public function getId() {
      return $this->id;
  }

  public function getHash() {
    return $this->hash;
  }

  public function getName() {
    return $this->name;
  }

  public function authenticate() {
      /** @var $user User */
      $user = User::model()->find('email = :email', array(':email' => $this->email));

      if ($user == null)
        $this->errorCode = self::ERROR_USERNAME_INVALID;
      elseif ($user->password != $user->hashPassword($this->password, $user->salt))
        $this->errorCode = self::ERROR_PASSWORD_INVALID;
      else {
        $this->errorCode = self::ERROR_NONE;

        $this->id = $user->id;
        $this->hash = $user->hash;
        $this->name = $user->login;
      }

      return $this->errorCode == self::ERROR_NONE;
  }
}