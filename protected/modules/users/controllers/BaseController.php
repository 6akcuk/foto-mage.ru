<?php

class BaseController extends Controller {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
    );
  }

  public function actionVKLogin($code) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1) ;
    curl_setopt($curl, CURLOPT_URL, 'https://api.vk.com/oauth/access_token?client_id='. Yii::app()->params['vk_client_id'] .'&client_secret='. Yii::app()->params['vk_client_secret']
     .'&code='. $code .'&redirect_uri=http://foto-mage.ru/vklogin');
    $data = curl_exec($curl);
    curl_close($curl);

    $response = json_decode($data, true);

    Yii::import('ext.VKHelper');

    $vk = new VKHelper($response['access_token']);
    $userdata = $vk->method('users.get', array(
      'user_id' => $response['user_id'],
      'fields' => 'photo_max_orig,city,sex',
    ));

    if (!isset($userdata['error'])) {
      $user = User::model()->find('sn_name = :name AND sn_uid = :uid', array(':name' => User::SN_NAME_VK, ':uid' => $response['user_id']));
      // Зарегистрировать нового пользователя
      if (!$user) {
        $user = new User('Users.addUser');
        $profile = new Profile('Users.addUser');
        $password = md5(rand(0, 100000));

        $user->email = 'vk'. $response['user_id'] .'_'. md5(rand(100, 99999));
        $user->salt = $user->generateSalt();
        $user->password = $user->hashPassword($password, $user->salt);
        $user->sn_name = User::SN_NAME_VK;
        $user->sn_uid = $response['user_id'];
        $user->sn_token = $response['access_token'];
        $user->sn_texpire = date("Y-m-d H:i:s", (time() + $response['expires_in']));

        $yupi = new Yupi(array(
          'enable_retries' => true,
        ));

        if ($user->validate()) {
          $user->save();

          $yupi = new Yupi(array(
            'enable_retries' => true,
          ));

          $photo = json_decode(
            $yupi->captureByCS('http://cs1.foto-mage.ru/capture.php', 'photo', $userdata['response'][0]['photo_max_orig']),
            true
          );

          /** @var City $city */
          if (isset($userdata['response'][0]['city'])) {
            $city = City::model()->find('name = :name', array(':name' => $userdata['response'][0]['city']['title']));
            if (!$city) {
              $city = new City();
              $city->name = $userdata['response'][0]['city']['title'];
              $city->timezone = 'Europe/Moscow';
              $city->save();
            }
          }

          $genders = array(1 => 'Female', 'Male');

          $profile->user_id = $user->id;
          $profile->firstname = $userdata['response'][0]['first_name'];
          $profile->lastname = $userdata['response'][0]['last_name'];
          $profile->gender = $genders[$userdata['response'][0]['sex']];
          $profile->photo = json_encode($photo['result']);

          if (isset($city))
            $profile->city_id = $city->id;

          if ($profile->validate()) {
            $profile->save();

            $authMgr = Yii::app()->getAuthManager();
            $authMgr->assign("Пользователь", $user->id);

            $loginform = new LoginForm();
            $loginform->email = $user->email;
            $loginform->password = $password;
            $loginform->login();

            $result['success'] = true;
            $result['message'] = 'Вы успешно зарегистрировались';

            echo "<script>window.opener.location = '/id". $user->id ."'; window.close()</script>";
          }
          else {
            $user->delete();

            $errors = array();
            foreach ($profile->getErrors() as $attr => $error) {
              $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
            }
            $result['message'] = implode('<br/>', $errors);
          }
        }
        else {
          $errors = array();
          foreach ($user->getErrors() as $attr => $error) {
            $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
          }
          $result['message'] = implode('<br/>', $errors);
        }
      } else {
        $identity = new UserIdentity($user->email, $user->password);
        $identity->id = $user->id;
        Yii::app()->user->login($identity);

        echo "<script>window.opener.location = '/id". $user->id ."'; window.close()</script>";
      }
    }


    //echo $access_token .' '. $user_id .'<br/>';

    //echo file_get_contents('https://api.vk.com/method/users.get?user_id='. $user_id .'&v=5.16');
  }

  public function actionLogin() {
    $model=new LoginForm;

    // collect user input data
    if(isset($_POST['LoginForm']))
    {
      $model->attributes=$_POST['LoginForm'];
      $result = array();

      if($model->validate() && $model->login()) {
        if (Yii::app()->getRequest()->isAjaxRequest) {
          $result['success'] = true;
          $result['id'] = Yii::app()->user->getId();
        }
        else {
          if (!isset($_SESSION['global.jumper'])) $this->redirect('/id'. Yii::app()->user->getId());
          else {
            $this->redirect($_SESSION['global.jumper']);
            unset($_SESSION['global.jumper']);
          }
        }
      }
      else {
        foreach ($model->getErrors() as $attr => $error) {
          $result[$attr] = $error;
        }
      }

      if (!Yii::app()->getRequest()->isAjaxRequest) {
        $_SESSION['LoginForm.errors'] = $result;
        $this->redirect('/id'. Yii::app()->user->getId());
      }

      echo json_encode($result);
      exit;
    }
  }

  public function actionLogout() {
    Yii::app()->user->logout();
    $this->redirect(Yii::app()->homeUrl);
  }

  public function _actionInvite($id) {
    $_SESSION['invite.id'] = $id;
    $this->redirect('/register');
  }

  public function actionRegister() {
    /** @var $user WebUser */
    $this->layout = '//layouts/main';

    //if (isset($_SESSION['invite.id']) && !$model->invite_code) $model->invite_code = $_SESSION['invite.id'];
    $user = new User('Users.addUser');
    $profile = new Profile('Users.addUser');

    if (isset($_POST['email'])) {
      $user->email = $_POST['email'];
      $user->salt = $user->generateSalt();
      $user->password = $user->hashPassword($_POST['password'], $user->salt);

      if ($user->validate()) {
        $user->save();

        $profile->user_id = $user->id;
        $profile->firstname = $_POST['firstname'];
        $profile->lastname = $_POST['lastname'];
        $profile->gender = $_POST['gender'];
        $profile->city_id = $_POST['city_id'];
        if ($profile->validate()) {
          $profile->save();

          $authMgr = Yii::app()->getAuthManager();
          $authMgr->assign("Пользователь", $user->id);

          $loginform = new LoginForm();
          $loginform->email = $user->email;
          $loginform->password = $_POST['password'];
          $loginform->login();

          $result['success'] = true;
          $result['message'] = 'Вы успешно зарегистрировались';
        }
        else {
          $user->delete();

          $errors = array();
          foreach ($profile->getErrors() as $attr => $error) {
            $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
          }
          $result['message'] = implode('<br/>', $errors);
          $result['id'] = $user->id;
        }
      }
      else {
        $errors = array();
        foreach ($user->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('register', array(), true);
    }
    else $this->render('register', array());
  }

  public function actionSendSMSRegister() {
    $user = Yii::app()->user;
    $model = new RegisterForm('step4');
    $model->attributes = $user->getState('regform', null);

    if ($model->phone) {
      PhoneConfirmation::model()->deleteAll('phone = :phone', array(':phone' => '7'. $model->phone));
      $pc = new PhoneConfirmation();
      $pc->phone = '7'. $model->phone;
      $pc->generateCode();
      $pc->save();

      $sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
      $sms->SendMessage($pc->phone, Yii::app()->params['smsNumber'], 'Ваш код регистрации '. $pc->code);

      echo json_encode(array(
        'success' => 'true',
        'step' => 4,
        'message' => 'Код отправлен',
      ));
    }
    else {
      echo json_encode(array(
        'message' => 'Данные неверны',
      ));
    }
    exit;
  }

  public function actionForgot() {
    $this->layout = '//layouts/main';

    // Вторая страница восстановления пароля
    if (isset($_GET['code'])) {
      /** @var $user User */
      $user = (isset($_GET['user_id'])) ? User::model()->findByPk($_GET['user_id']) : User::model()->find('email = :mail', array(':mail' => $_GET['email']));

      if (!$user)
        throw new CHttpException(500, 'Пользователь не найден');

      if (strtotime($user->pwdresetstamp) < (time() - 300))
        throw new CHttpException(500, 'Сгенерируйте новый код восстановления, т.к. истек срок действия текущего');

      if ($user->pwdresetfaults > 5)
        throw new CHttpException(500, 'Вы ошиблись более 5 раз при вводе кода восстановления, сгенерируйте новый');

      if ($user->pwdresethash != $_GET['code']) {
        $user->pwdresetfaults++;
        $user->save(true, array('pwdresetfaults'));

        throw new CHttpException(500, 'Код восстановления не совпадает');
      }

      if (isset($_POST['new_password'])) {
        $new_pwd = trim($_POST['new_password']);
        $rpt_pwd = trim($_POST['new_password_rpt']);

        if ($new_pwd != $rpt_pwd) {
          $result['message'] = 'Пароли не совпадают';

          echo json_encode($result);
          exit;
        }

        if (strlen($new_pwd) < 3) {
          $result['message'] = 'Длина пароля не меньше 3-х символов';

          echo json_encode($result);
          exit;
        }

        $user->password = $user->hashPassword($new_pwd, $user->salt);
        $user->pwdresetfaults = 0;
        $user->pwdresethash = null;
        $user->pwdresetstamp = null;

        $user->save(true, array('password', 'pwdresetfaults', 'pwdresethash', 'pwdresetstamp'));

        // Сообщить об изменениях
        //$sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
        //$sms->SendMessage($user->profile->phone, Yii::app()->params['smsNumber'], 'На вашем аккаунте '. $user->email .' был изменен пароль');

        Yii::import('application.vendors.*');
        require_once 'Mail/Mail.php';

        $mail = Mail::getInstance();
        $mail->setSender(array(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname']));
        $mail->IsMail();

        $html = $this->renderPartial("//mail/report_change_password", array('password' => $new_pwd), true);

        $mail->sendMail(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname'], $user->email, 'Смена пароля на Foto-Mage', $html, true, null, null, null);
        $mail->ClearAddresses();

        $result = array('success' => true, 'message' => 'Пароль успешно изменен');
        echo json_encode($result);
        exit;
      }

      if (Yii::app()->request->isAjaxRequest) {
        $this->pageHtml = $this->renderPartial('forgot2', array('code' => $_GET['code'], 'email' => $user->email), true);
      }
      else $this->render('forgot2', array('code' => $_GET['code'], 'email' => $user->email));
      return;
    }

    if (isset($_POST['email'])) {
      $email = $_POST['email'];
      $type = 'email'; //$_POST['type'];
      /** @var $user User */
      $user = User::model()->with('profile')->find('email = :mail', array(':mail' => $email));

      $result = array();

      if (!$user) {
        $result['message'] = 'Данный E-Mail не зарегистрирован на сайте';
      } else {
        switch ($type) {
          case 'cellular':
            $pc = new PhoneConfirmation();
            $pc->generateCode();
            $user->pwdresetfaults = 0;
            $user->pwdresethash = $pc->code;
            $user->pwdresetstamp = date("Y-m-d H:i:s");
            $user->save(true, array('pwdresetfaults', 'pwdresethash', 'pwdresetstamp'));

            $sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
            $sms->SendMessage($user->profile->phone, Yii::app()->params['smsNumber'], 'Код восстановления '. $pc->code .'. Проигнорируйте, если вы не запрашивали.');

            $result['success'] = true;
            $result['message'] = 'Код восстановления отправлен на Ваш сотовый телефон '. preg_replace("/.*([0-9]{4})$/i", "*******$1", $user->profile->phone);
            break;
          case 'email':
            $user->pwdresetfaults = 0;
            $user->pwdresethash = md5(rand(1000000, 9999999) . $user->email);
            $user->pwdresetstamp = date("Y-m-d H:i:s");
            $user->save(true, array('pwdresetfaults', 'pwdresethash', 'pwdresetstamp'));

            Yii::import('application.vendors.*');
            require_once 'Mail/Mail.php';

            $mail = Mail::getInstance();
            $mail->setSender(array(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname']));
            $mail->IsMail();

            $html = $this->renderPartial("//mail/forgot_password", array('id' => $user->id, 'code' => $user->pwdresethash), true);

            $mail->sendMail(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname'], $user->email, 'Восстановление доступа к Foto-Mage', $html, true, null, null, null);
            $mail->ClearAddresses();

            $result['success'] = true;
            $result['message'] = 'Код восстановления отправлен на указанный Вами E-Mail';
            break;
        }
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('forgot', null, true);
    }
    else $this->render('forgot', null);
  }
}