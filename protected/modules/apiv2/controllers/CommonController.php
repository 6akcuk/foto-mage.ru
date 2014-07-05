<?php

class CommonController extends ApiController
{
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  public function actionSaveToken($token) {
    $this->authorize();

    Yii::app()->user->model->iOSDeviceToken = $token;
    Yii::app()->user->model->save(true, array('iOSDeviceToken'));

    exit;
  }

  public function actionSaveAndroidToken($token) {
    $this->authorize();

    Yii::app()->user->model->AndroidPushToken = $token;
    Yii::app()->user->model->save(true, array('AndroidPushToken'));

    exit;
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

  public function actionRemember() {
    Yii::import('users.models.*');

    $phone = $_POST['phone'];

    /** @var User $user */
    $user = User::model()->find('email = :phone', array(':phone' => $phone));
    if ($user) {
      $password = rand(100000, 999999);

      $user->salt = $user->generateSalt();
      $user->password = $user->hashPassword($password, $user->salt);
      if ($user->save(true, array('salt', 'password'))) {
        SMSHelper::send(preg_replace("/^8(\d+)/ui", "+7$1", $user->email), 'Ваш новый пароль для входа в WoC: '. $password);
        $this->json = array('success' => true);
      }
    }
    else throw new CHttpException(500, 'Телефон не зарегистрирован');
  }

  public function actionChangePassword() {
    $this->authorize();

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

  public function actionSendCode() {
    $phone = preg_replace('#[^0-9]#', '', $_POST['phone']);
    PhoneConfirmation::model()->deleteAll('phone = :phone', array(':phone' => $phone));
    $pc = new PhoneConfirmation();
    $pc->phone = $phone;
    $pc->generateSession();
    $pc->generateCode();
    $pc->save();

    //$sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
    if (!isset($_POST['nosend'])) {
      $result = SMSHelper::send(preg_replace("/^8(\d+)/ui", "+7$1", $pc->phone), 'Код подтверждения в WoC: '. $pc->code .'.');
      //$result = $sms->SendMessage(preg_replace("/^8(\d+)/ui", "+7$1", $pc->phone), Yii::app()->params['smsNumber'], 'Код подтверждения: '. $pc->code .'.');
    } else {
      $result = true;
    }
    if (!$result) {
      throw new CHttpException(500, 'Не удалось отправить СМС');
    }
    else {
      $this->json = array('success' => true);
    }
  }

  public function actionRegister() {
    Yii::import('users.models.*');

    $phone = preg_replace('#[^0-9]#', '', $_POST['phone']);
    $password = $_POST['password'];
    $name = $_POST['name'];
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

  public function actionGetCities() {
    $cities = City::model()->findAll('');
    foreach ($cities as $city) {
      $this->json[] = array('id' => $city->id, 'name' => $city->name);
    }
  }

  public function actionGetWeather($city_id) {
    /** @var WeatherLink $weather */
    /** @var City $city */
    $city = City::model()->findByPk($city_id);

    $today = new DateTime('now', new DateTimeZone('Europe/Moscow'));
    $today->setTimezone(new DateTimeZone($city->timezone));

    $weather = WeatherLink::model()->with('forecast')->find('city_id = :id', array(':id' => $city_id));
    $forecast = new SimpleXMLElement($weather->forecast->data);

    $temperature = (string) $forecast->fact->temperature;
    if (!stristr($temperature, '-') && intval($temperature) > 0) $temperature = "+". $temperature;
    $week = array();

    $day_seek = 1;
    foreach ($forecast->day as $day) {
      if ($day['date'] == $today->format('Y-m-d')) continue;
      if ($day_seek == 6) break;

      $date = new DateTime($day['date']);
      $week[] = array(
        'name' => Yii::t('app', 'неделя_'. $date->format('w')),
        'from' => (string) $day->day_part[4]->temperature,
        'to' => (string) $day->day_part[5]->temperature,
      );

      $day_seek++;
    }

    $this->json = array(
      'weekday' => Yii::t('app', 'неделя_'. $today->format('w')),
      'temperature' => $temperature,
      'weather_type' => (string) $forecast->fact->weather_type,
      'week' => $week,
    );
  }
}