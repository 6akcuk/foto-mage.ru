<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 20.03.13
 * Time: 11:49
 * To change this template use File | Settings | File Templates.
 */

class ChangeEmailForm extends CFormModel {
  public $new_mail;

  public function rules() {
    return array(
      array('new_mail', 'required'),
      array('new_mail', 'email'),
    );
  }

  public function attributeLabels() {
    return array(
      'new_mail' => 'Новый адрес',
    );
  }

}