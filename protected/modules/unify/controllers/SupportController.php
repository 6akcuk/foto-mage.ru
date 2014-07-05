<?php

class SupportController extends Controller {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
      array(
        'ext.RBACFilter.RBACFilter'
      )
    );
  }

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('unify')->supportsPerPage;
    $criteria->offset = $offset;

    if (isset($c['q'])) $criteria->addSearchCondition('msg', $c['q']);
    if (isset($c['status']) && $c['status']) $criteria->compare('status', $c['status']);

    $supports = Support::model()->findAll($criteria);
    $supportsNum = Support::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('index', array(
        'supports' => $supports,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $supportsNum,
      ), true);
    }
    else $this->render('index', array(
      'supports' => $supports,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $supportsNum,
    ));
  }

  public function actionAnswer($id) {
    $support = Support::model()->findByPk($id);
    if (!$support)
      throw new CHttpException(404, 'Сообщение поддержки не было найдено');
    if (!isset($_POST['msg']))
      throw new CHttpException(500, 'Отсутствует сообщение для пользователя');

    $support->status = Support::STATUS_PROCESSED;
    $support->save(true, array('status'));

    Yii::import('application.vendors.*');
    require_once 'Mail/Mail.php';

    $mail = Mail::getInstance();
    $mail->setSender(array(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname']));
    $mail->IsMail();

    $mail->sendMail(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname'], $support->email, 'e-Bash.me - Техническая поддержка', nl2br($_POST['msg']), true, null, null, null);
    $mail->ClearAddresses();

    echo json_encode(array(
      'success' => true,
      'message' => 'Сообщение успешно отправлено',
    ));
    exit;
  }

  /**
   * Support Linkin Park Recharge Album (2013)
   *
   * @param $id
   * @throws CHttpException
   */
  public function actionRecharge($id) {
    $support = Support::model()->findByPk($id);
    if (!$support)
      throw new CHttpException(404, 'Сообщение поддержки не было найдено');
    if (!isset($_POST['status']))
      throw new CHttpException(500, 'Не передан статус сообщения');

    $support->status = $_POST['status'];
    $support->save(true, array('status'));

    echo json_encode(array(
      'success' => true
    ));
    exit;
  }
}