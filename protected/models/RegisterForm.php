<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 22.11.12
 * Time: 10:21
 * To change this template use File | Settings | File Templates.
 */

class RegisterForm extends CFormModel {
    public $city;
    public $gender;
    public $lastname;
    public $firstname;
    public $middlename;
  public $invite_code;
    public $login;
    public $email;
    public $password;
    public $phone;
    public $agreement;
    public $confirm;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
          array('invite_code', 'numerical'),
            // Step 1 Scenario
            array('city', 'required', 'on' => 'step1', 'message' => '{attribute} не может быть пустым'),
            array('city', 'numerical', 'integerOnly' => true, 'on' => 'step1'),
            // Step 2 Scenario
            array('city, gender, lastname, firstname, middlename', 'required', 'on' => 'step2', 'message' => 'Заполните поле {attribute}'),
            array('lastname, firstname, middlename', 'length', 'on' => 'step2', 'min' => 2),
            // Step 3 Scenario
            array('city, gender, lastname, firstname, middlename, login, email, password', 'required', 'on' => 'step3', 'message' => '{attribute} не может быть пустым'),
            array('login', 'length', 'min' => 3, 'max' => 30),
            array('login', 'unique', 'on' => 'step3', 'className' => 'User'),
            array('email', 'email', 'on' => 'step3'),
            array('email', 'unique', 'on' => 'step3', 'className' => 'User', 'message' => '{attribute} \'{value}\' уже используется'),
            array('password', 'length', 'on' => 'step3', 'min' => 3),
            // Step 4 Scenario
            array('city, gender, lastname, firstname, middlename, login, email, password, phone, agreement', 'required', 'on' => 'step4', 'message' => '{attribute} не может быть пустым'),
            array('phone', 'length', 'on' => 'step4', 'min' => 10),
            array('phone', 'unique', 'on' => 'step4', 'className' => 'Profile'),
            // Step 5 Scenario
            array('city, gender, lastname, firstname, middlename, login, email, password, phone, agreement, confirm', 'required', 'on' => 'step5', 'message' => '{attribute} не может быть пустым'),
            array('confirm', 'checkConfirm', 'on' => 'step5'),
        );
    }

    public function checkConfirm($attribute, $params) {
        if (!$this->hasErrors()) {
            $pc = PhoneConfirmation::model()->find('phone = :phone', array(':phone' => $this->phone));
            if ($pc->code != $this->confirm)
                $this->addError('confirm', 'Код подтверждения не совпадает с указанным');
        }
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'city' => 'Город',
            'gender' => 'Пол',
            'lastname' => 'Фамилия',
            'firstname' => 'Имя',
            'middlename' => 'Отчество',
          'invite_code' => 'Номер приглашения',
            'login' => 'Логин',
            'email' => 'E-Mail',
            'password' => 'Пароль',
            'phone' => 'Мобильный телефон',
            'agreement' => 'Я согласен получать информационную и рекламную рассылку в виде смс-сообщений на указанный номер телефона',
            'confirm' => 'Код подтверждения',
        );
    }
}