<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 06.01.13
 * Time: 23:47
 * To change this template use File | Settings | File Templates.
 */

class FriendsController extends Controller {
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

        if (isset($_GET['section'])) {
            $this->defaultAction = $_GET['section'];
        }
    }

    public function actionIndex($id = 0, $offset = 0) {
        /** @var $user User */
        $user = ($id == 0) ? Yii::app()->user->model : User::model()->findByPk($id);
        $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

        $peoples = $user->profile->getAllFriends($c, $offset);
        $peoplesNum = $user->profile->countAllFriends($c);

        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_POST['pages'])) {
                $this->pageHtml = $this->renderPartial('_people', array(
                    'user' => $user,
                    'peoples' => $peoples,
                    'offset' => $offset,
                ), true);
            }
            else $this->pageHtml = $this->renderPartial('index', array(
                'user' => $user,
                'peoples' => $peoples,
                'c' => $c,
                'offset' => $offset,
                'offsets' => $peoplesNum,
            ), true);
        }
        else $this->render('index', array('user' => $user, 'peoples' => $peoples, 'c' => $c, 'offset' => $offset, 'offsets' => $peoplesNum,));
    }

    public function actionOnline($id = 0, $offset = 0) {
        /** @var $user User */
        $user = ($id == 0) ? Yii::app()->user->model : User::model()->findByPk($id);
        $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

        $peoples = $user->profile->getOnlineFriends($c, $offset);
        $peoplesNum = $user->profile->countOnlineFriends($c);

        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_POST['pages'])) {
                $this->pageHtml = $this->renderPartial('_people', array(
                    'user' => $user,
                    'peoples' => $peoples,
                    'offset' => $offset,
                ), true);
            }
            else $this->pageHtml = $this->renderPartial('online', array(
                'user' => $user,
                'peoples' => $peoples,
                'c' => $c,
                'offset' => $offset,
                'offsets' => $peoplesNum,
            ), true);
        }
        else $this->render('online', array('user' => $user, 'peoples' => $peoples, 'c' => $c, 'offset' => $offset, 'offsets' => $peoplesNum,));
    }

    public function actionRequests($offset = 0) {
        if ($this->pageCounters['friends'] > 0) {
            $peoples = Yii::app()->user->model->profile->getCurrentFriendRequests($offset);
            $peoplesNum = $this->pageCounters['friends'];

            if (Yii::app()->request->isAjaxRequest) {
                if (isset($_POST['pages'])) {
                    $this->pageHtml = $this->renderPartial('_request', array(
                        'peoples' => $peoples,
                        'offset' => $offset,
                    ), true);
                }
                else $this->pageHtml = $this->renderPartial('requests', array(
                    'peoples' => $peoples,
                    'offset' => $offset,
                    'offsets' => $peoplesNum,
                ), true);
            }
            else $this->render('requests', array('peoples' => $peoples, 'offset' => $offset, 'offsets' => $peoplesNum,));
        }
        else $this->redirect('/friends?section=allRequests');
    }

    public function actionAllRequests($offset = 0) {
        $peoples = Yii::app()->user->model->profile->getAllSubscribers($offset);
        $peoplesNum = Yii::app()->user->model->profile->countAllSubscribers();

        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_POST['pages'])) {
                $this->pageHtml = $this->renderPartial('_request', array(
                    'peoples' => $peoples,
                    'offset' => $offset,
                ), true);
            }
            else $this->pageHtml = $this->renderPartial('allrequests', array(
                'peoples' => $peoples,
                'offset' => $offset,
                'offsets' => $peoplesNum,
            ), true);
        }
        else $this->render('allrequests', array('peoples' => $peoples, 'offset' => $offset, 'offsets' => $peoplesNum,));
    }

    public function actionOutRequests($offset = 0) {
        $peoples = Yii::app()->user->model->profile->getAllOutFriendRequests($offset);
        $peoplesNum = Yii::app()->user->model->profile->countAllOutFriendRequests();

        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_POST['pages'])) {
                $this->pageHtml = $this->renderPartial('_request', array(
                    'peoples' => $peoples,
                    'offset' => $offset,
                ), true);
            }
            else $this->pageHtml = $this->renderPartial('outrequests', array(
                'peoples' => $peoples,
                'offset' => $offset,
                'offsets' => $peoplesNum,
            ), true);
        }
        else $this->render('outrequests', array('peoples' => $peoples, 'offset' => $offset, 'offsets' => $peoplesNum,));
    }

    public function actionAdd() {
      $result = ProfileRelationship::addToFriend(Yii::app()->user->model, $_POST['friend_id']);
      $messages = array(
        0 => 'Пользователь не найден',
        1 => 'Заявка успешно отправлена',
        2 => 'Пользователь успешно добавлен в друзья',
        -1 => 'Не удалось подтвердить заявку',
        -2 => 'Заявка уже подана',
        -3 => 'Не удалось отправить заявку',
      );
      $success = ($result > 0) ? true : false;

      echo json_encode(array('success' => $success, 'message' => $messages[$result]));
      exit;
    }

    public function actionDelete() {
      /** @var $friend User  */
      $friend_id = intval($_POST['friend_id']);
      $friend = User::model()->findByPk($friend_id);

      if (!$friend) {
        echo json_encode(array('success' => false, 'message' => 'Пользователь не найден'));
        exit;
      }

      $relation = $friend->profile->getProfileRelation();

      if ($relation) {
        if (Yii::app()->user->model->profile->isFriend($relation)) {
          $relation->rel_type = ($relation->from_id == Yii::app()->user->getId()) ? ProfileRelationship::TYPE_INCOME : ProfileRelationship::TYPE_OUTCOME;
          $relation->save(true, array('rel_type'));

          echo json_encode(array(
            'success' => true, 'message' => $friend->getDisplayName() .' '. Yii::t('user', '0#убран|1#убрана', $friend->profile->genderToInt()) .' из друзей и '. Yii::t('user', '0#переведен|1#переведена', $friend->profile->genderToInt()) .' в разряд подписчиков',
          ));
          exit;
        }
        elseif(Yii::app()->user->model->profile->isProfileRelationOutcome($relation)) {
          $relation->delete();
          $request = ProfileRequest::model()->find('owner_id = :id AND req_type = :type AND req_link_id = :link_id', array(
            ':id' => $friend->id,
            ':type' => ProfileRequest::TYPE_FRIEND,
            ':link_id' => $relation->rel_id,
          ));
          if ($request) $request->delete();

          echo json_encode(array(
            'success' => true, 'message' => 'Вы отписались от обновлений '. $friend->login,
          ));
          exit;
        }
      }
      else {
        echo json_encode(array('success' => false, 'message' => 'Вы не связаны'));
        exit;
      }
    }

    public function actionKeep() {
      /** @var $friend User  */
      $friend_id = intval($_POST['friend_id']);
      $friend = User::model()->findByPk($friend_id);

      if (!$friend) {
        echo json_encode(array('success' => false, 'message' => 'Пользователь не найден'));
        exit;
      }

      $relation = $friend->profile->getProfileRelation();

      if ($relation) {
        if(Yii::app()->user->model->profile->isProfileRelationIncome($relation)) {
          $request = ProfileRequest::model()->find('(owner_id = :id OR owner_id = :oid) AND req_type = :type AND req_link_id = :link_id', array(
            ':id' => $friend->id,
            ':oid' => Yii::app()->user->getId(),
            ':type' => ProfileRequest::TYPE_FRIEND,
            ':link_id' => $relation->rel_id,
          ));

          if ($request) $request->delete();

          echo json_encode(array(
            'success' => true, 'message' => $friend->getDisplayName() .' '. Yii::t('user', '0#оставлен|1#оставлена', $friend->profile->genderToInt()) .' в подписчиках',
          ));
          exit;
        }
      }
      else {
        echo json_encode(array('success' => false, 'message' => 'Вы не связаны'));
        exit;
      }
    }

  public function actionGetFriends($query) {
    /** @var ProfileRelationship $friend */
    $friends = Yii::app()->user->model->profile->getAllFriends(array('name' => $query));
    $data = array();

    foreach ($friends as $friend) {
      $data[] = array(
        $friend->friend->id,
        $friend->friend->getDisplayName(),
        false,
        false,
        0,
        $friend->friend->profile->city->name,
        ActiveHtml::getPhotoUrl($friend->friend->profile->photo, 'c'),
      );
    }

    echo json_encode($data);
    exit;
  }
}