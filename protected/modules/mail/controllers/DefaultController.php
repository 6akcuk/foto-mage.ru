<?php

class DefaultController extends Controller
{
  public $defaultAction = 'inbox';

  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
      array(
        'ext.RBACFilter.RBACFilter'
      ),
    );
  }

  public function init() {
    parent::init();

    if (isset($_GET['act']))
      $this->defaultAction = $_GET['act'];
  }

  public function actionInbox($filter = 'all', $offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $c['filter'] = $filter;

    $messages = DialogMessage::getInboxMessages(Yii::app()->user->getId(), $offset, $c);
    $offsets = DialogMessage::countInboxMessages(Yii::app()->user->getId(), $c);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_message', array(
          'messages' => $messages,
          'offset' => $offset,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('inbox', array(
        'messages' => $messages,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $offsets,
      ), true);
    }
    else $this->render('inbox', array(
      'messages' => $messages,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $offsets,
    ));
  }

  public function actionOutbox($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $messages = DialogMessage::getOutboxMessages(Yii::app()->user->getId(), $offset, $c);
    $offsets = DialogMessage::countOutboxMessages(Yii::app()->user->getId(), $c);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_message', array(
          'messages' => $messages,
          'offset' => $offset,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('outbox', array(
        'messages' => $messages,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $offsets,
      ), true);
    }
    else $this->render('outbox', array(
      'messages' => $messages,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $offsets,
    ));
  }

  public function actionShow($id) {
    /** @var $message DialogMessage */
    $message = DialogMessage::model()->with('dialog', 'dialog.membersNum', 'author', 'author.profile', array('isNewIn' => array('joinType' => 'LEFT JOIN')))->findByPk($id);

    $found = false;
    foreach ($message->dialog->members as $member) {
      if ($member->member_id == Yii::app()->user->getId()) {
        $found = true;
        break;
      }
    }

    if ($found) {
      if ($message->isNewIn) {
        $message->isNewIn->delete();
        $this->pageCounters['pm']--;
      }

      if (Yii::app()->request->isAjaxRequest) $this->pageHtml = $this->renderPartial('show', array(
        'message' => $message,
      ), true);
      else $this->render('show', array(
        'message' => $message,
      ));
    }
    else throw new CHttpException(403, 'В доступе отказано');
  }

  public function actionWrite($id = 0) {
    if (isset($_POST['recipients'])) {
      $recipients = $_POST['recipients'];
      $message = $_POST['message'];

      $photo = array();
      $doc = array();

      // Прикрепленные фотографии
      if (isset($_POST['attaches']['photo'])) {
        foreach ($_POST['attaches']['photo'] as $ph) {
          $photo[] = json_decode($ph, true);
        }
      }
      // Прикрепленные документы
      if (isset($_POST['attaches']['document'])) {
        foreach ($_POST['attaches']['document'] as $dc) {
          $doc[] = json_decode($dc, true);
        }
      }

      $result = DialogMessage::send($recipients, $message, '', array('photo' => $photo, 'doc' => $doc));

      echo json_encode($result);
      exit;
    }

    $friends = Yii::app()->user->model->profile->getAllFriends(null);
    $guest = ($id) ? User::model()->with('profile', 'profile.city')->findByPk($id) : null;

    if (Yii::app()->request->isAjaxRequest) $this->pageHtml = $this->renderPartial('write', array(
      'friends' => $friends,
      'guest' => $guest,
    ), true);
    else $this->render('write', array(
      'friends' => $friends,
      'guest' => $guest,
    ));
  }

  public function actionHistory($id, $offset = 0) {
    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('mail')->messagesPerPage;
    $criteria->offset = $offset;
    $criteria->compare('t.dialog_id', $id);
    $criteria->compare('member.member_id', Yii::app()->user->getId());
    $criteria->order = 't.creation_date DESC';

    $messages = DialogMessage::model()->with('author.profile', 'dialog.member')->findAll($criteria);

    $criteria->limit = 0;
    $offsets = DialogMessage::model()->with('author.profile', 'dialog.member')->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_history', array(
          'messages' => $messages,
          'offset' => $offset,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('history', array(
        'id' => $id,
        'messages' => $messages,
        'offset' => $offset,
        'offsets' => $offsets,
      ), true);
    }
    else $this->render('history', array(
      'id' => $id,
      'messages' => $messages,
      'offset' => $offset,
      'offsets' => $offsets,
    ));
  }

  public function actionSend() {
    $id = intval($_POST['dialog_id']);
    $result = array();

    $criteria = new CDbCriteria();
    $criteria->addCondition('dialog_id = :id');
    $criteria->addCondition('member_id = :mid');

    $criteria->params = array(
      ':id' => $id,
      ':mid' => Yii::app()->user->getId(),
    );

    $member = DialogMember::model()->find($criteria);
    if ($member) {
      $message = $_POST['message'];

      $photo = array();
      $doc = array();

      // Прикрепленные фотографии
      if (isset($_POST['attaches']['photo'])) {
        foreach ($_POST['attaches']['photo'] as $ph) {
          $photo[] = json_decode($ph, true);
        }
      }
      // Прикрепленные документы
      if (isset($_POST['attaches']['document'])) {
        foreach ($_POST['attaches']['document'] as $dc) {
          $doc[] = json_decode($dc, true);
        }
      }

      $data = DialogMessage::mail($id, $message, array('photo' => $photo, 'doc' => $doc));
      if ($result > 0) {
        $result['success'] = true;
        $result['msg'] = 'Сообщение успешно отправлено';
        $result['url'] = '/mail?act=inbox';
      }
      else {
        $result['success'] = false;
        $result['msg'] = 'Не удалось отправить сообщение';
      }
    }
    else {
      $result['success'] = false;
      $result['msg'] = 'Вы не состоите в этом диалоге';
    }

    echo json_encode($result);
    exit;
  }

  public function actionDelete_selected() {
    $items = $_POST['items'];
    $criteria = new CDbCriteria();
    $criteria->addInCondition('message_id', $items);
    $back = array();

    $messages = DialogMessage::model()->with('dialog', 'dialog.members')->findAll($criteria);
    /** @var $message DialogMessage */
    foreach ($messages as $message) {
      if ($message->dialog->type != Dialog::TYPE_TET) continue;

      $found = false;
      foreach ($message->dialog->members as $member) {
        if ($member->member_id == Yii::app()->user->getId()) $found = true;
      }

      if (!$found) continue;

      $message->message_delete = date("Y-m-d H:i:s");
      $message->save(true, array('message_delete'));

      $del = new CDbCriteria();
      $del->compare('req_link_id', $message->message_id);
      $del->compare('req_type', ProfileRequest::TYPE_PM);
      $del->compare('owner_id', Yii::app()->user->getId());
      ProfileRequest::model()->deleteAll($del);

      $back[] = $message->message_id;
    }

    $criteria = new CDbCriteria();
    $criteria->addCondition('owner_id = :id');
    $criteria->addCondition('viewed = 0');
    $criteria->addCondition('req_type = :type');
    $criteria->params[':id'] = Yii::app()->user->getId();
    $criteria->params[':type'] = ProfileRequest::TYPE_PM;
    $this->pageCounters['pm'] = ProfileRequest::model()->count($criteria);

    echo json_encode(array('success' => true, 'backlist' => $back, 'pm' => $this->pageCounters['pm']));
    exit;
  }

  public function actionRestore($id) {
    $message = DialogMessage::model()->resetScope()->with('dialog', 'dialog.members')->findByPk($id);

    if ($message->dialog->type == Dialog::TYPE_TET) {
      $found = false;
      foreach ($message->dialog->members as $member) {
        if ($member->member_id == Yii::app()->user->getId()) $found = true;
      }

      if ($found) {
        $message->message_delete = null;
        $message->save(true, array('message_delete'));

        echo json_encode(array('success' => true));
        exit;
      }
    }

    echo json_encode(array('success' => false));
    exit;
  }

  public function actionMark_readed() {
    $items = $_POST['items'];
    $criteria = new CDbCriteria();
    $criteria->addInCondition('message_id', $items);
    $back = array();

    $messages = DialogMessage::model()->with('dialog', 'dialog.members', array('isNew' => array('joinType' => 'LEFT JOIN')))->findAll($criteria);
    /** @var $message DialogMessage */
    foreach ($messages as $message) {
      if (!$message->isNew) continue;
      elseif ($message->author_id == Yii::app()->user->getId()) continue;

      $found = false;
      foreach ($message->dialog->members as $member) {
        if ($member->member_id == Yii::app()->user->getId()) $found = true;
      }

      if (!$found) continue;

      $del = new CDbCriteria();
      $del->compare('req_link_id', $message->message_id);
      $del->compare('req_type', ProfileRequest::TYPE_PM);
      $del->compare('owner_id', Yii::app()->user->getId());
      ProfileRequest::model()->deleteAll($del);

      $back[] = $message->message_id;
    }

    $criteria = new CDbCriteria();
    $criteria->addCondition('owner_id = :id');
    $criteria->addCondition('viewed = 0');
    $criteria->addCondition('req_type = :type');
    $criteria->params[':id'] = Yii::app()->user->getId();
    $criteria->params[':type'] = ProfileRequest::TYPE_PM;
    $this->pageCounters['pm'] = ProfileRequest::model()->count($criteria);

    echo json_encode(array('success' => true, 'backlist' => $back, 'pm' => $this->pageCounters['pm']));
    exit;
  }

  public function actionMark_new() {
    $items = $_POST['items'];
    $criteria = new CDbCriteria();
    $criteria->addInCondition('message_id', $items);
    $back = array();

    $messages = DialogMessage::model()->with('dialog', 'dialog.members', array('isNew' => array('joinType' => 'LEFT JOIN')))->findAll($criteria);
    /** @var $message DialogMessage */
    foreach ($messages as $message) {
      if ($message->isNew) continue;
      elseif ($message->author_id == Yii::app()->user->getId()) continue;

      $found = false;
      foreach ($message->dialog->members as $member) {
        if ($member->member_id == Yii::app()->user->getId()) $found = true;
      }

      if (!$found) continue;

      $request = new ProfileRequest();
      $request->req_type = ProfileRequest::TYPE_PM;
      $request->req_link_id = $message->message_id;
      $request->owner_id = Yii::app()->user->getId();
      $request->save();

      $back[] = $message->message_id;
    }

    $criteria = new CDbCriteria();
    $criteria->addCondition('owner_id = :id');
    $criteria->addCondition('viewed = 0');
    $criteria->addCondition('req_type = :type');
    $criteria->params[':id'] = Yii::app()->user->getId();
    $criteria->params[':type'] = ProfileRequest::TYPE_PM;
    $this->pageCounters['pm'] = ProfileRequest::model()->count($criteria);

    echo json_encode(array('success' => true, 'backlist' => $back, 'pm' => $this->pageCounters['pm']));
    exit;
  }
}