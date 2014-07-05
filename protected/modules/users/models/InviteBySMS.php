<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.03.13
 * Time: 18:21
 * To change this template use File | Settings | File Templates.
 */

class InviteBySMS extends CFormModel {
  public $phone;
  public $name;

  public function rules() {
    return array(
      array('name, phone', 'required'),
      array('phone', 'length', 'min' => 11, 'max' => 11),
    );
  }

  public function attributeLabels() {
    return array(
      'name' => 'Имя друга',
      'phone' => 'Номер телефона (11 цифр, начинается с 7)',
    );
  }
}