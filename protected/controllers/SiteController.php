<?php

class SiteController extends Controller
{
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
    );
  }

  public function actionAutoReportPhpError() {
    $report = new Autoreport();
    $report->user_id = Yii::app()->user->getId();
    $report->datetime = date("Y-m-d H:i:s");
    $report->url = substr($_POST['url'], 0, 100);
    $report->response = $_POST['text'];
    if (!$report->save()) {
      var_dump($report->getErrors());
    }

    exit;
  }

  public function actionBstn() {
    $u = new User();
    $salt = $u->generateSalt();
    $pwd = $u->hashPassword('s1a55j7', $salt);

    echo 'Salt = `'. $salt .'`, Pwd = `'. $pwd .'`';

    exit;
  }

  public function actionViewMailTpl($template) {
    $this->renderPartial('//mail/'. $template);
    Yii::app()->end();
  }

  public function actionSupport() {
    $this->layout = '//layouts/main';
    $support = new Support();
    $success_msg = false;

    if (isset($_POST['msg'])) {
      $support->name = $_POST['name'];
      $support->email = $_POST['email'];
      $support->msg = $_POST['msg'];

      if ($support->save()) {
        $success_msg = "Ваше сообщение успешно отправлено";

        $support = new Support();
      }
    }

    $this->render('support', array('support' => $support, 'success_msg' => $success_msg));
  }

  public function actionPrivacy() {
    $this->layout = '//layouts/main';

    $this->render('privacy');
  }

	public function actionIndex()
	{
    if (!Yii::app()->user->getIsGuest()) {
      //$this->redirect('/');
    }

    $this->render('index');
	}

  public function actionSetCity() {
      $cookies = Yii::app()->getRequest()->getCookies();
      $cookies->remove('cur_city');

      $city = new CHttpCookie('cur_city', intval($_POST['city_id']));
      $city->expire = time() + (60 * 60 * 24 * 30 * 12 * 20);
      $cookies->add('cur_city', $city);

      echo json_encode(array('success' => true, 'msg' => 'Изменения сохранены'));
      exit;
  }

  public function actionError() {
    if($error=Yii::app()->errorHandler->error)
    {
      if (
        stristr(Yii::app()->request->getUrl(), '/api/') ||
        stristr(Yii::app()->request->getUrl(), '/apiv2/')
      ) {
        $this->pageHtml = $error['message'];

        header('Content-type: application/json');
        echo json_encode(array('result' => Yii::app()->controller->json, 'code' => $error['code'], 'message' => Yii::app()->controller->pageHtml));
        Yii::app()->end();
      }
      else {
        if(Yii::app()->request->isAjaxRequest)
          $this->pageHtml = $error['message'];
        else
          $this->render('error', $error);
      }
    }
  }
}