<?php

class ApiController extends Controller {
  protected function authorize($lazy = false) {
    if (isset($_POST['user_id']) && isset($_POST['hash'])) {
      $usr = User::model()->findByPk($_POST['user_id']);
      if ($usr && $usr->password == $_POST['hash']) {
        $identity = new UserIdentity('', '');
        $identity->id = $usr->id;
        $identity->hash = $usr->password;
        Yii::app()->user->login($identity);
        Yii::app()->user->init();

        return true;
      }
      else {
        if (!$lazy) throw new CHttpException(401, 'Данные авторизации неверны');
        return false;
      }
    }
    else return false;
  }
}