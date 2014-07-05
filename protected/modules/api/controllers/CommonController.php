<?php

class CommonController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  public function actionSaveToken($token) {
    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Требуется авторизация');

    Yii::app()->user->model->iOSDeviceToken = $token;
    Yii::app()->user->model->save(true, array('iOSDeviceToken'));

    exit;
  }

  public function actionCheck() {
    $user = User::model()->findByPk($_POST['user_id']);
    if ($user) {
      if ($user->password == $_POST['hash']) {
        $this->json = array('user_id' => $user->id, 'hash' => $user->password);
      }
      else throw new CHttpException(500, 'Пароль неверный');
    }
    else throw new CHttpException(500, 'Пользователь не найден');
  }

  public function actionLogin() {
    Yii::import('users.models.*');

    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $user = User::model()->find('email = :phone', array(':phone' => $phone));
    if ($user) {
      if ($user->password == $user->hashPassword($password, $user->salt)) {
        $this->json = array('user_id' => $user->id, 'hash' => $user->password, 'name' => $user->login);
      }
      else throw new CHttpException(500, 'Пароль неверный');
    }
    else throw new CHttpException(500, 'Телефон не зарегистрирован');
  }

  public function actionLoginV2() {
    $model=new LoginForm;

    // collect user input data
    if(isset($_POST['LoginForm']))
    {
      $model->attributes=$_POST['LoginForm'];
      $result = array();

      if($model->validate() && $model->login()) {
        $result['success'] = true;
        $result['id'] = Yii::app()->user->getId();
        $result['name'] = Yii::app()->user->model->login;
        $result['hash'] = Yii::app()->user->getHash();
      }
      else {
        foreach ($model->getErrors() as $attr => $error) {
          $result['errors'][] = (is_array($error)) ? implode("\r\n", $error) : $error;
        }
      }

      if (isset($result['errors']))
        $result['errors'] = implode("\r\n", $result['errors']);

      $this->json = $result;
    }
  }

  public function actionGetCode() {
    $phone = preg_replace('#[^0-9]#', '', $_POST['phone']);
    PhoneConfirmation::model()->deleteAll('phone = :phone', array(':phone' => $phone));
    $pc = new PhoneConfirmation();
    $pc->phone = $phone;
    $pc->generateSession();
    $pc->generateCode();
    $pc->save();

    //$sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
    if (!isset($_POST['nosend'])) {
      $result = SMSHelper::send(preg_replace("/^8(\d+)/ui", "+7$1", $pc->phone), 'Код подтверждения: '. $pc->code .'.');
      //$result = $sms->SendMessage(preg_replace("/^8(\d+)/ui", "+7$1", $pc->phone), Yii::app()->params['smsNumber'], 'Код подтверждения: '. $pc->code .'.');
    } else {
      $result = true;
    }
    if (!$result) {
      throw new CHttpException(500, 'Не удалось отправить СМС: '. $sms->errorMsg);
    }
    else {
      $this->json = array('success' => true);
    }
  }

  public function actionRegisterV2() {
    $model=new LoginForm;
    $result = array();

    Yii::import('users.models.*');

    $phone = preg_replace('#[^0-9]#', '', $_POST['phone']);
    $password = $_POST['password'];
    $name = $_POST['name'];
    $city = $_POST['city'];
    $code = $_POST['code'];

    $pc = PhoneConfirmation::model()->find('phone = :phone', array(':phone' => $phone));
    if ($pc->code != $code) {
      throw new CHttpException(500, 'Код подтверждения не совпадает');
    }

    $user = new User('api.common.register');
    $user->email = $phone;
    $user->login = $name;
    $user->salt = $user->generateSalt();
    $user->password = $user->hashPassword($password, $user->salt);
    if ($user->save()) {
      $profile = new Profile();
      $profile->user_id = $user->id;
      $profile->city_id = $city;
      if ($profile->save()) {
        $authMgr = Yii::app()->getAuthManager();
        $authMgr->assign("Пользователь", $user->id);

        $model->email = $user->email;
        $model->password = $password;
        $model->login();

        $result['success'] = true;
        $result['id'] = Yii::app()->user->getId();
        $result['name'] = $user->login;
        $result['hash'] = Yii::app()->user->getHash();

        $this->json = $result;
      }
      else {
        $user->delete();
        $errors = array();
        foreach ($profile->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }

        throw new CHttpException(500, implode('<br/>', $errors));
      }
    } else {
      $errors = array();
      foreach ($user->getErrors() as $attr => $error) {
        $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
      }

      throw new CHttpException(500, implode('<br/>', $errors));
    }
  }

  public function actionRegister() {
    Yii::import('users.models.*');

    $phone = preg_replace('#[^0-9]#', '', $_POST['phone']);
    $password = $_POST['password'];
    $name = $_POST['name'];
    $city = $_POST['city'];
    $code = $_POST['code'];

    $pc = PhoneConfirmation::model()->find('phone = :phone', array(':phone' => $phone));
    if ($pc->code != $code) {
      throw new CHttpException(500, 'Код подтверждения не совпадает');
    }

    $user = new User('api.common.register');
    $user->email = $phone;
    $user->login = $name;
    $user->salt = $user->generateSalt();
    $user->password = $user->hashPassword($password, $user->salt);
    if ($user->save()) {
      $profile = new Profile();
      $profile->user_id = $user->id;
      $profile->city_id = $city;
      if ($profile->save()) {
        $authMgr = Yii::app()->getAuthManager();
        $authMgr->assign("Пользователь", $user->id);

        $this->json = array('user_id' => $user->id, 'hash' => $user->password, 'name' => $user->login);
      }
      else {
        $user->delete();
        $errors = array();
        foreach ($profile->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }

        throw new CHttpException(500, implode('<br/>', $errors));
      }
    } else {
      $errors = array();
      foreach ($user->getErrors() as $attr => $error) {
        $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
      }

      throw new CHttpException(500, implode('<br/>', $errors));
    }
  }

  public function actionCities() {
    $cities = City::model()->findAll('published = 1');
    foreach ($cities as $city) {
      $this->json[] = array('id' => $city->id, 'name' => $city->name);
    }
  }

  public function actionChangeCity() {
    if (!$this->authorize())
      throw new CHttpException(403, 'Вы не авторизованы');

    if (isset($_POST['city_id'])) {
      Yii::app()->user->model->profile->city_id = $_POST['city_id'];

      $result = array();

      if (Yii::app()->user->model->profile->save(true, array('city_id'))) {
        $result['success'] = true;
        $result['message'] = 'Город успешно изменен';
      }
      else {
        $errors = array();
        foreach (Yii::app()->user->model->profile->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['errors'] = implode('<br/>', $errors);
      }

      $this->json = $result;
    }
    else {
      $city_idx = 0;

      $criteria = new CDbCriteria();
      $criteria->compare('published', 1);
      $criteria->order = 'name ASC';

      $cities = City::model()->findAll($criteria);
      $items = array();
      foreach ($cities as $city) {
        $items[] = array('id' => $city->id, 'name' => $city->name);
      }

      foreach ($items as $idx => $item) {
        if ($item['id'] == Yii::app()->user->model->profile->city_id) $city_idx = $idx;
      }

      $this->json = array(
        'items' => $items,
        'city_idx' => intval($city_idx),
      );
    }
  }

  public function actionChangePassword() {
    if (!$this->authorize())
      throw new CHttpException(403, 'Вы не авторизованы');

    $usr = new User();
    $hash = $usr->hashPassword($_POST['old_password'], Yii::app()->user->model->salt);

    if ($hash == Yii::app()->user->model->password) {
      $salt = $usr->generateSalt();
      $new_hash = $usr->hashPassword($_POST['new_password'], $salt);

      Yii::app()->user->model->salt = $salt;
      Yii::app()->user->model->password = $new_hash;

      $result = array();

      if (Yii::app()->user->model->save(true, array('password', 'salt'))) {
        $result['success'] = true;
        $result['message'] = 'Пароль успешно изменен';
        $result['hash'] = $new_hash;
      }
      else {
        $errors = array();
        foreach (Yii::app()->user->model->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['errors'] = implode('<br/>', $errors);
      }
    }
    else {
      throw new CHttpException(500, 'Текущий пароль неверный');
    }

    $this->json = $result;
  }

  public function actionFeedback() {
    $this->authorize();

    //if (Yii::app()->user->checkAccess('users.feedback.add')) {
      $feedback = new Feedback();
      $feedback->author_id = Yii::app()->user->getId();
      $feedback->message = $_POST['message'];

      $result = array();

      if ($feedback->save()) {
        $result['success'] = true;
        $result['message'] = 'Сообщение успешно отправлено';
      }
      else {
        $errors = array();
        foreach ($feedback->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['errors'] = implode('<br/>', $errors);
      }

      $this->json = $result;
    /*} else {
      throw new CHttpException(403, 'У Вас нет прав на отправку сообщений обратной связи');
    }*/
  }

  public function actionWeather($city_id = null, $sync = null) {
    $this->authorize();

    /** @var WeatherLink $weather */
    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;
    /** @var City $city */
    $city = City::model()->findByPk($city_id);

    $weather = WeatherLink::model()->with('forecast')->find('city_id = :id', array(':id' => $city_id));
    $forecast = new SimpleXMLElement($weather->forecast->data);

    $temperature = (string) $forecast->fact->temperature;
    if (!stristr($temperature, '-')) $temperature = "+". $temperature;

    if ($sync) {
      //$sync = floor($sync / 1000);

      $s = new DateTime(urldecode($sync), new DateTimeZone($city->timezone));
      $d = new DateTime('now', new DateTimeZone('Europe/Moscow'));
      $d->setTimezone(new DateTimeZone($city->timezone));
      //$d->setTimezone(new DateTimeZone("Etc/". $tz));
      $diff = $d->diff($s);

      //throw new CHttpException(500, $s->format('Y-m-d H:i:s') .' - '. $d->format('Y-m-d H:i:s'));

      $sync = ($diff->s + $diff->i * 60 + $diff->h * 3600); //$d->getTimestamp() - $sync;
      if ($diff->invert == 0) $sync = -$sync;
    }

    $this->json = array(
      'city' => (string) $forecast['city'],
      'temperature' => $temperature,
      'weather_type' => (string) $forecast->fact->weather_type_short,
      'sync' => $sync,
    );
  }
}
