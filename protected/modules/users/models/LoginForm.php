<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 25.09.12
 * Time: 15:40
 * To change this template use File | Settings | File Templates.
 */

class LoginForm extends CFormModel {
    public $email;
    public $password;

    private $_identity;

    public function rules() {
        return array(
            array('email, password', 'required'),
            array('password', 'authenticate'),
        );
    }

    public function attributeLabels() {
        return array(
            'email' => 'E-Mail',
            'password' => 'Пароль',
        );
    }

    public function authenticate($attribute, $params) {
        if (!$this->hasErrors()) {
            $this->_identity = new UserIdentity($this->email, $this->password);

            if (!$this->_identity->authenticate())
                $this->addError('password', 'Неверные данные');
        }
    }

    public function login() {
        if($this->_identity===null)
        {
            $this->_identity=new UserIdentity($this->email,$this->password);
            $this->_identity->authenticate();
        }
        if($this->_identity->errorCode===UserIdentity::ERROR_NONE)
        {
            //$duration=$this->rememberMe ? 3600*24*30 : 0; // 30 days
            Yii::app()->user->login($this->_identity);
            return true;
        }
        else {
            echo $this->_identity->errorCode;
            return false;
        }
    }
}