<?php

class ChangePhoneForm extends CFormModel {
  public $phone;
  public $code;

  public function rules() {
    return array(
      array('phone', 'required', 'on' => 'receive_code'),
      array('phone, code', 'required', 'on' => 'change_phone'),
      array('phone', 'phoneValidator'),
      array('code', 'numerical', 'min' => 100000, 'max' => 999999, 'integerOnly' => true, 'on' => 'change_phone'),
    );
  }

  public function attributeLabels() {
    return array(
      'phone' => 'Мобильный телефон',
      'code' => 'Полученный код',
    );
  }

  public function phoneValidator($attribute, $params) {
    if (!$this->hasErrors()) {
      $phone = preg_replace("/[\(\)\-+]*/ui", "", $this->phone);

      if (!preg_match("/\d{11}/ui", $phone)) {
        $this->addError('phone', 'Номер телефона должен быть указан в международном формате. Например, +7(965)000-00-00');
      }
    }
  }
}