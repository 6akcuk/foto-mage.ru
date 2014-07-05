<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 10.04.13
 * Time: 15:54
 * To change this template use File | Settings | File Templates.
 */

class EmailNotifyForm extends CFormModel {
  public $im;
  public $purchases;
  public $comments;
  public $orders;
  public $payments;

  public function rules() {
    return array(
      array('im, purchases, comments', 'required'),
      array('orders, payments', 'required', 'on' => 'org'),
      array('im, purchases, comments, orders, payments', 'numerical', 'min' => 0, 'max' => 1, 'integerOnly' => true),
    );
  }

  public function attributeLabels() {
    return array(
      'im' => 'Личные сообщения',
      'purchases' => 'Новые закупки',
      'comments' => 'Комментарии к товару',
      'orders' => 'Заказы',
      'payments' => 'Платежи',
    );
  }
}