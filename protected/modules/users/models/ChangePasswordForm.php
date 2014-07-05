<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 17.03.13
 * Time: 0:22
 * To change this template use File | Settings | File Templates.
 */

class ChangePasswordForm extends CFormModel {
  public $old_password;
  public $new_password;
  public $rpt_password;

  public function rules() {
    return array(
      array('old_password, new_password, rpt_password', 'required'),
      array('old_password', 'checkOld'),
      array('new_password', 'compare', 'compareAttribute' => 'rpt_password'),
    );
  }

  public function attributeLabels() {
    return array(
      'old_password' => 'Старый пароль',
      'new_password' => 'Новый пароль',
      'rpt_password' => 'Повторите пароль',
    );
  }

  public function checkOld($attribute, $params) {
    if (!$this->hasErrors()) {
      /** @var $user User */
      $user = Yii::app()->user->model;
      $old_password = $user->hashPassword($this->old_password, $user->salt);

      if ($old_password != $user->password)
        $this->addError('old_password', 'Старый пароль не совпадает с текущим');
    }
  }
}