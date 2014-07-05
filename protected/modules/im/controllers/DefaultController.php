<?php

class DefaultController extends Controller
{
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

        if (isset($_GET['action']))
            $this->defaultAction = $_GET['action'];

        if (isset($_GET['sel']) && $_GET['sel'] == -1)
            $this->defaultAction = 'create';
        elseif (isset($_GET['sel']) && $_GET['sel'] > 0)
            $this->defaultAction = 'show';
    }

    public function actionIndex($offset = 0)
	{
        $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

        $dialogs = Dialog::getDialogs(Yii::app()->user->getId(), $offset);

        $criteria = new CDbCriteria();
        $criteria->addCondition('member.member_id = :id');
        $criteria->params[':id'] = Yii::app()->user->getId();
        $criteria->limit = 0;
        $criteria->group = 'dialog.dialog_id';
        $dialogsNum = Dialog::model()->with('members')->count('members.member_id = :id', array(':id' => Yii::app()->user->getId())); //DialogMember::model()->with('dialog', 'dialog.lastMessage')->count($criteria);

        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_POST['pages'])) {
                $this->pageHtml = $this->renderPartial('_dialog', array(
                    'dialogs' => $dialogs,
                    'offset' => $offset,
                ), true);
            }
            else $this->pageHtml = $this->renderPartial('index', array(
                'dialogs' => $dialogs,
                'c' => $c,
                'offset' => $offset,
                'offsets' => $dialogsNum,
            ), true);
        }
        else $this->render('index', array('dialogs' => $dialogs, 'c' => $c, 'offset' => $offset, 'offsets' => $dialogsNum,));
	}

    public function actionCreate($id = 0) {
      if (isset($_POST['recipients'])) {
          $recipients = $_POST['recipients'];
          $title = $_POST['title'];
          $message = $_POST['message'];

          $result = DialogMessage::send($recipients, $message, $title);

          echo json_encode($result);
          exit;
      }

      $friends = Yii::app()->user->model->profile->getAllFriends(null);
      $guest = ($id) ? User::model()->with('profile', 'profile.city')->findByPk($id) : null;
      $msg = (isset($_POST['msg'])) ? urldecode($_POST['msg']) : '';

      if (Yii::app()->request->isAjaxRequest) {
        $this->boxWidth = 502;
        $this->pageHtml = $this->renderPartial((isset($_POST['box_request'])) ? 'create_box' : 'create', array('friends' => $friends, 'guest' => $guest, 'msg' => $msg), true);
      }
      else $this->render('create', array('friends' => $friends, 'guest' => $guest));
    }

    public function actionShow($sel, $offset = 0) {
        $criteria = new CDbCriteria();
        $criteria->limit = Yii::app()->getModule('im')->messagesPerPage;
        $criteria->offset = $offset;

        $criteria->addCondition('dialog_id = :id');
        $criteria->params[':id'] = $sel;

        $criteria->order = 'creation_date DESC';

        $dialog = Dialog::model()->with(array('members' => array('limit' => 4)))->findByPk($sel);
        $messages = DialogMessage::model()->with('author', array('isNewIn' => array('joinType' => 'LEFT JOIN')), array('isNewOut' => array('joinType' => 'LEFT JOIN')))->findAll($criteria);

        $messages = array_reverse($messages);

        $criteria->limit = 0;
        $messagesNum = DialogMessage::model()->count($criteria);

        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_POST['pages'])) {
                $this->pageHtml = $this->renderPartial('_im', array(
                    'messages' => $messages,
                    'offset' => $offset,
                ), true);
            }
            else $this->pageHtml = $this->renderPartial('show', array(
                'dialog' => $dialog,
                'messages' => $messages,
                'offset' => $offset,
                'offsets' => $messagesNum,
            ), true);
        }
        else $this->render('show', array('dialog' => $dialog, 'messages' => $messages, 'offset' => $offset, 'offsets' => $messagesNum,));
    }

    public function actionSend($id) {
      $criteria = new CDbCriteria();
      $criteria->addCondition('dialog_id = :id');
      $criteria->addCondition('member_id = :mid');

      $criteria->params = array(
        ':id' => $id,
        ':mid' => Yii::app()->user->getId(),
      );

      $member = DialogMember::model()->find($criteria);
        if ($member) {
            $message = new DialogMessage();
            $message->dialog_id = $id;
            $message->author_id = Yii::app()->user->getId();
            $message->message = $_POST['msg'];

            if ($message->save()) {
                $dialogMembers = DialogMember::model()->findAll('dialog_id = :id', array(':id' => $id));

                /** @var $member DialogMember */
                $selfRecipient = 0;
                foreach ($dialogMembers as $member) {
                    // Можно отправлять самому себе сообщения
                    if ($member->member_id == Yii::app()->user->getId()) {
                        $selfRecipient++;
                    }
                    else {
                        $request = new ProfileRequest();
                        $request->owner_id = $member->member_id;
                        $request->req_type = ProfileRequest::TYPE_PM;
                        $request->req_link_id = $message->message_id;
                        $request->save();
                    }
                }

                $success = true;
                $msg = '';
            }
            else {
                $success = false;
                $msg = 'Ошибка при отправке сообщения';
            }
        }
        else {
            $success = false;
            $msg = 'Вы не состоите в этом диалоге';
        }

        echo json_encode(array('success' => $success, 'message' => $msg));
        exit;
    }

    public function actionPeer($id, $timestamp) {
        $criteria = new CDbCriteria();
        $criteria->addCondition('dialog_id = :id');
        $criteria->addCondition('member_id = :mid');

        $criteria->params = array(
            ':id' => $id,
            ':mid' => Yii::app()->user->getId(),
        );

        $member = DialogMember::model()->find($criteria);
        if ($member) {
            $criteria = new CDbCriteria();
            $criteria->addCondition('dialog_id = :id');
            $criteria->addCondition('creation_date > :date');
            $criteria->params[':id'] = $id;
            $criteria->params[':date'] = date("Y-m-d H:i:s", $timestamp);

            $criteria->order = 'creation_date DESC';

            $messages = DialogMessage::model()->with('author', array('isNewIn' => array('joinType' => 'LEFT JOIN')), array('isNewOut' => array('joinType' => 'LEFT JOIN')))->findAll($criteria);
            $messages = array_reverse($messages);

            $html = $this->renderPartial('_im', array(
                'messages' => $messages,
            ), true);

            echo json_encode(array('success' => true, 'html' => $html, 'counters' => $this->pageCounters));
        }
        else {
            echo json_encode(array('success' => false, 'message' => 'Вы не состоите в этом диалоге'));
        }

        exit;
    }

    public function actionViewed($id) {
        $criteria = new CDbCriteria();
        $criteria->addCondition('req_type = :type');
        $criteria->addCondition('req_link_id = :id');
        $criteria->addCondition('owner_id = :owner');

        $criteria->params = array(
            ':type' => ProfileRequest::TYPE_PM,
            ':id' => $id,
            ':owner' => Yii::app()->user->getId(),
        );
        $request = ProfileRequest::model()->find($criteria);
        if ($request && $request->delete()) {
            $this->pageCounters['pm']--;
            $success = true;
        }
        else $success = false;

        echo json_encode(array('success' => $success, 'counters' => $this->pageCounters));
        exit;
    }
}