<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 23.11.12
 * Time: 22:39
 * To change this template use File | Settings | File Templates.
 */

class ProfilesController extends Controller {
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

    public function actionIndex($id) {
      /** @var User $userinfo */
      $userinfo = User::model()->with('profile')->findByPk($id);
      $friends = $userinfo->profile->getFriends();
      $friendsNum = $userinfo->profile->countFriends();
      $friendsOnline = $userinfo->profile->getOnlineFriends();
      $friendsOnlineNum = $userinfo->profile->countOnlineFriends();

      $criteria = new CDbCriteria();
      $criteria->limit = Yii::app()->getModule('users')->wallPostsPerPage;
      $criteria->order = 't.add_date DESC';
      $criteria->addCondition('t.reply_to IS NULL');
      $criteria->addCondition('t.post_delete IS NULL');
      $criteria->compare('t.wall_id', $id);

      $posts = ProfileWallPost::model()->with('author', 'author.profile', array('last_replies.replyTo' => array('limit' => 3)), 'repliesNum')->findAll($criteria);

      $criteria->limit = 0;
      $postsNum = ProfileWallPost::model()->count($criteria);

      if (Yii::app()->request->isAjaxRequest) {
          $this->pageHtml = $this->renderPartial('index', array(
            'userinfo' => $userinfo,
            'friends' => $friends,
            'friendsNum' => $friendsNum,
            'friendsOnline' => $friendsOnline,
            'friendsOnlineNum' => $friendsOnlineNum,
            'posts' => $posts,
            'postsNum' => $postsNum,
          ), true);
      }
      else $this->render('index', array(
        'userinfo' => $userinfo,
        'friends' => $friends,
        'friendsNum' => $friendsNum,
        'friendsOnline' => $friendsOnline,
        'friendsOnlineNum' => $friendsOnlineNum,
        'posts' => $posts,
        'postsNum' => $postsNum,
      ));
    }

  public function actionStatus() {
    /** @var $profile Profile */
    $profile = Yii::app()->user->model->profile;
    $profile->status = htmlspecialchars($_POST['status']);
    if ($profile->save(true, array('status'))) {
      $feed = new Feed();
      $feed->owner_type = 'user';
      $feed->owner_id = Yii::app()->user->getId();
      $feed->event_type = 'new status';
      $feed->event_link_id = Yii::app()->user->getId();
      $feed->event_text = htmlspecialchars($_POST['status']);
      $feed->save();
    }

    echo json_encode(array('status' => true));
    exit;
  }

  public function actionWall($id, $offset = 0, $post_id = 0, $reply = 0) {
    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('users')->wallPostsPerPage;
    $criteria->offset = $offset;
    $criteria->order = 't.add_date DESC';
    $criteria->addCondition('t.reply_to IS NULL');
    $criteria->addCondition('t.post_delete IS NULL');
    $criteria->compare('t.wall_id', $id);

    if ($post_id == 0) {
      $post = null;
      $posts = ProfileWallPost::model()->with('author.profile', array('last_replies.replyTo' => array('limit' => 3)), 'repliesNum')->findAll($criteria);

      $criteria->limit = 0;
      $postsNum = ProfileWallPost::model()->count($criteria);
    }
    else {
      $posts = array();

      $post = ($reply)
        ? ProfileWallPost::model()->with('author.profile', array('last_replies.replyTo' => array('condition' => 'last_replies.post_id >= :id', 'params' => array(':id' => $reply))), 'repliesNum')->findByPk($post_id)
        : ProfileWallPost::model()->with('author.profile', array('last_replies.replyTo' => array('limit' => 3)), 'repliesNum')->findByPk($post_id);
      $postsNum = 1;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_wall', array(
          'posts' => $posts,
          'offset' => $offset,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('wall', array(
        'id' => $id,
        'post' => $post,
        'reply' => $reply,
        'posts' => $posts,
        'offset' => $offset,
        'offsets' => $postsNum,
      ), true);
    }
    else $this->render('wall', array(
      'id' => $id,
      'post' => $post,
      'reply' => $reply,
      'posts' => $posts,
      'offset' => $offset,
      'offsets' => $postsNum,
    ));
  }

  public function actionWallPost($id) {
    $wall = new ProfileWallPost('secure');
    $wall->author_id = Yii::app()->user->getId();
    $wall->wall_id = $id;
    $wall->post = htmlspecialchars($_POST['wall']['post']);
    if (isset($_POST['wall']['reply_to'])) $wall->reply_to = $_POST['wall']['reply_to'];
    if (isset($_POST['wall']['reply_to_title'])) $wall->reply_to_id = $_POST['wall']['reply_to_title'];
    $wall->attaches = json_encode(isset($_POST['wall']['attach']) ? $_POST['wall']['attach'] : array());
    $wall->save();

    if ($wall->reply_to > 0) {
      $criteria = new CDbCriteria();
      $criteria->compare('wall_id', $id);
      $criteria->compare('reply_to', $wall->reply_to);
      $criteria->addCondition('post_delete IS NULL');

      $repliesNum = ProfileWallPost::model()->count($criteria);

      $criteria->addCondition('post_id > :id');
      $criteria->params[':id'] = intval($_POST['last_id']);

      $replies = ProfileWallPost::model()->findAll($criteria);

      echo json_encode(array(
        'num' => 'Показать все '. Yii::t('app', '{n} комментарий|{n} комментария|{n} комментариев', $repliesNum),
        'replies' => $this->renderPartial('_reply', array('_reply' => 0, 'replies' => $replies), true),
        'last_id' => ($replies) ? $replies[sizeof($replies) - 1]->post_id : intval($_POST['last_id']),
      ));
    }
    else {
      $criteria = new CDbCriteria();
      $criteria->compare('wall_id', $id);
      $criteria->addCondition('reply_to IS NULL');
      $criteria->addCondition('post_delete IS NULL');

      $postsNum = ProfileWallPost::model()->count($criteria);

      $criteria->addCondition('post_id > :id');
      $criteria->params[':id'] = intval($_POST['last_id']);

      $posts = ProfileWallPost::model()->findAll($criteria);

      echo json_encode(array(
        'num' => Yii::t('app', '{n} запись|{n} записи|{n} записей', $postsNum),
        'posts' => $this->renderPartial('_wall', array('posts' => $posts, 'offset' => 0), true),
        'last_id' => ($posts) ? $posts[sizeof($posts) - 1]->post_id : intval($_POST['last_id']),
      ));
    }
    exit;
  }

  public function actionPostReplies($post_id) {
    $criteria = new CDbCriteria();
    $criteria->compare('reply_to', $post_id);
    $criteria->addCondition('post_delete IS NULL');
    $criteria->addCondition('post_id < :id');
    $criteria->params[':id'] = intval($_POST['first_id']);

    $replies = ProfileWallPost::model()->with('author', 'author.profile', 'replyTo')->findAll($criteria);

    echo json_encode(array('html' => $this->renderPartial('_reply', array('_reply' => 0, 'replies' => $replies), true)));
    exit;
  }

  public function actionDeleteWallPost($id) {
    /** @var $post ProfileWallPost */
    $post = ProfileWallPost::model()->findByPk($id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('post' => $post)) ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Owner', array('post' => $post))) {
      $post->markAsDeleted();

      echo json_encode(array());
      exit;
    }
    else
      throw new CHttpException(403, 'В доступе отказано');
  }

  public function actionRestoreWallPost($id) {
    /** @var $post ProfileWallPost */
    $post = ProfileWallPost::model()->resetScope()->findByPk($id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('post' => $post)) ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Owner', array('post' => $post))) {
      $post->restore();

      echo json_encode(array());
      exit;
    }
    else
      throw new CHttpException(403, 'В доступе отказано');
  }


  public function actionReputation($id, $offset = 0) {
        $act = (isset($_GET['act']))  ? $_GET['act'] : 'show';
        $value = (isset($_POST['value'])) ? intval($_POST['value']) : 0;
        $comment = (isset($_POST['comment'])) ? trim($_POST['comment']) : '';

        switch ($act) {
            case 'increase':
                if ($value <= 0) {
                    throw new CHttpException(500, 'Неверное значение репутации');
                    return;
                }

                if ($id == Yii::app()->user->getId()) {
                    throw new CHttpException(500, 'Нельзя '. Yii::t('app', '0#самому|1#самой', Yii::app()->user->model->profile->genderToInt()) .' себе повышать репутацию');
                    return;
                }

                if (Yii::app()->user->checkAccess('users.profiles.increaseReputation') &&
                    (
                        (!Yii::app()->user->checkAccess('users.profiles.increaseReputationAny') &&
                            in_array($value, array(1, 5))
                        )
                        ||
                        Yii::app()->user->checkAccess('users.profiles.increaseReputationAny')
                    )
                )
                {
                    $reputation = new ProfileReputation();
                    $reputation->author_id = Yii::app()->user->getId();
                    $reputation->owner_id = $id;
                    $reputation->value = $value;
                    $reputation->comment = $comment;

                    if (!$reputation->save()) {
                        throw new CHttpException(500, 'Не удалось создать запись в БД');
                        return;
                    }

                    $conn = $reputation->getDbConnection();
                    $command = $conn->createCommand("UPDATE `profiles` SET positive_rep = positive_rep + ". $value ." WHERE `user_id` = ". $id);
                    if (!$command->execute()) {
                        $reputation->delete();
                        throw new CHttpException(500, 'Не удалось увеличить репутацию пользователю');
                        return;
                    }

                    $profile = Profile::model()->findByPk($id);
                    echo json_encode(array('positive_rep' => $profile->positive_rep));
                    exit;
                }
                else {
                    throw new CHttpException(403, 'В доступе отказано');
                }
                break;
            case 'decrease':
                if ($value <= 0) {
                    throw new CHttpException(500, 'Неверное значение репутации');
                    return;
                }

                if ($id == Yii::app()->user->getId()) {
                    throw new CHttpException(500, 'Нельзя '. Yii::t('app', '0#самому|1#самой', Yii::app()->user->model->profile->genderToInt()) .' себе понижать репутацию');
                    return;
                }

                if (Yii::app()->user->checkAccess('users.profiles.decreaseReputation') &&
                    (
                        (!Yii::app()->user->checkAccess('users.profiles.decreaseReputationAny') &&
                            in_array($value, array(1, 5))
                        )
                            ||
                            Yii::app()->user->checkAccess('users.profiles.decreaseReputationAny')
                    ) &&
                    !Yii::app()->user->checkAccess('users.profiles.decreaseReputationUser')
                )
                {
                    $reputation = new ProfileReputation();
                    $reputation->author_id = Yii::app()->user->getId();
                    $reputation->owner_id = $id;
                    $reputation->value = -$value;
                    $reputation->comment = $comment;

                    if (!$reputation->save()) {
                        throw new CHttpException(500, 'Не удалось создать запись в БД');
                        return;
                    }

                    $conn = $reputation->getDbConnection();
                    $command = $conn->createCommand("UPDATE `profiles` SET negative_rep = negative_rep + ". $value ." WHERE `user_id` = ". $id);
                    if (!$command->execute()) {
                        $reputation->delete();
                        throw new CHttpException(500, 'Не удалось уменьшить репутацию пользователю');
                        return;
                    }

                    $profile = Profile::model()->findByPk($id);
                    echo json_encode(array('negative_rep' => $profile->negative_rep));
                    exit;
                }
                else {
                    throw new CHttpException(403, 'В доступе отказано');
                }
                break;
            case 'show':
                $user = User::model()->with('profile')->findByPk($id);

                $criteria = new CDbCriteria();
                $criteria->limit = Yii::app()->getModule('users')->reputationPerPage;
                $criteria->offset = $offset;
                $criteria->order = 'rep_date DESC';
                $criteria->addCondition('owner_id = :id');
                $criteria->params[':id'] = $id;

                $reputations = ProfileReputation::model()->with('author', 'author.profile')->findAll($criteria);

                $criteria->limit = 0;
                $reputationsNum = ProfileReputation::model()->count($criteria);

                if (Yii::app()->request->isAjaxRequest) {
                    if (isset($_POST['pages'])) {
                        $this->pageHtml = $this->renderPartial('_reputation', array(
                            'data' => $reputations,
                            'offset' => $offset,
                        ), true);
                    }
                    else $this->pageHtml = $this->renderPartial('reputation', array(
                        'user' => $user,
                        'data' => $reputations,
                        'offset' => $offset,
                        'offsets' => $reputationsNum,
                    ), true);
                }
                else $this->render('reputation', array('user' => $user, 'data' => $reputations, 'offset' => $offset, 'offsets' => $reputationsNum,));

                break;
        }
    }

    public function actionDeleteReputation() {
        $id = intval($_POST['id']);
        $reputation = ProfileReputation::model()->findByPk($id);

        if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
            Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('reputation' => $reputation))) {
            $reputation->reputation_delete = date("Y-m-d H:i:s");
            $value = ($reputation->value < 0) ? -$reputation->value : $reputation->value;
            $field = ($reputation->value < 0) ? 'negative_rep' : 'positive_rep';

            if (!$reputation->save(true, array('reputation_delete'))) {
                throw new CHttpException(500, 'Не удалось установить маркер удаления');
                return;
            }

            $conn = $reputation->getDbConnection();
            $command = $conn->createCommand("UPDATE `profiles` SET `". $field ."` = `". $field ."` - ". $value ." WHERE `user_id` = ". $reputation->owner_id);
            if (!$command->execute()) {
                $reputation->reputation_delete = null;
                $reputation->save(true, array('reputation_delete'));

                throw new CHttpException(500, 'Не удалось обновить репутацию пользователя');
                return;
            }

            $_SESSION['reputation.'. $id .'.hash'] = $hash = substr(md5(time() . $id . 'tt'), 0, 8);

            echo json_encode(array('success' => true, 'html' => 'Репутация удалена. <a onclick="return Profile.restoreReputation('. $id .', \''. $hash .'\')">Восстановить</a>'));
            exit;
        }
        else
            throw new CHttpException(403, 'В доступе отказано');
    }
    public function actionRestoreReputation() {
        $id = intval($_POST['id']);
        $reputation = ProfileReputation::model()->resetScope()->findByPk($id);

        if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
            Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('reputation' => $reputation))) {
            if (!isset($_SESSION['reputation.'. $id .'.hash']) || $_SESSION['reputation.'. $id .'.hash'] != $_POST['hash']) {
                throw new CHttpException(500, 'Неверный формат запроса');
                exit;
            }

            $reputation->reputation_delete = null;
            $value = ($reputation->value < 0) ? -$reputation->value : $reputation->value;
            $field = ($reputation->value < 0) ? 'negative_rep' : 'positive_rep';

            if (!$reputation->save(true, array('reputation_delete'))) {
                throw new CHttpException(500, 'Не удалось снять маркер удаления');
                exit;
            }

            $conn = $reputation->getDbConnection();
            $command = $conn->createCommand("UPDATE `profiles` SET `". $field ."` = `". $field ."` + ". $value ." WHERE `user_id` = ". $reputation->owner_id);
            if (!$command->execute()) {
                $reputation->reputation_delete = date("Y-m-d H:i:s");
                $reputation->save(true, array('reputation_delete'));

                throw new CHttpException(500, 'Не удалось обновить репутацию пользователя');
                exit;
            }

            unset($_SESSION['reputation.'. $id .'.hash']);

            echo json_encode(array('success' => true));
            exit;
        }
        else
            throw new CHttpException(403, 'В доступе отказано');
    }

  public function actionInviteBySMS() {
    $invite = new InviteBySMS();
    $invite->attributes = (isset($_POST['InviteBySMS'])) ? $_POST['InviteBySMS'] : array();

    $result = array();

    if ($invite->validate()) {
      $sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
      $sms->SendMessage($invite->phone, Yii::app()->params['smsNumber'], $invite->name .', удобный сайт оптовых закупок SPMIX.ru. Приглашение №'. Yii::app()->user->getId());

      $result['success'] = true;
      $result['msg'] = 'Сообщение успешно отправлено';
    }
    else {
      foreach ($invite->getErrors() as $attr => $error) {
        $result[ActiveHtml::activeId($invite, $attr)] = $error;
      }
    }

    echo json_encode($result);
    exit;
  }

  public function actionEdit() {
    /** @var $userinfo User */
    $userinfo = User::model()->with('profile')->findByPk(Yii::app()->user->getId());

    if (isset($_POST['firstname'])) {
      $userinfo->profile->setScenario('edit');

      $userinfo->profile->firstname = $_POST['firstname'];
      $userinfo->profile->lastname = $_POST['lastname'];
      $userinfo->profile->gender = $_POST['gender'];
      $userinfo->profile->city_id = $_POST['city_id'];
      $userinfo->profile->photo = $_POST['photo'];

      if ($userinfo->profile->save(true, array('firstname', 'lastname', 'gender', 'city_id', 'photo'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        foreach ($userinfo->profile->getErrors() as $attr => $error) {
          $result[ActiveHtml::activeId($userinfo->profile, $attr)] = $error;
        }
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
        $this->pageHtml = $this->renderPartial('edit', array('userinfo' => $userinfo), true);
    }
    else $this->render('edit', array('userinfo' => $userinfo));
  }

  public function actionSettings() {
    $changepwdmdl = new ChangePasswordForm();
    $changeemailmdl = new ChangeEmailForm();

    $err = '';
    $report = '';

    /** Удаленные действия через E-Mail */
    if (isset($_GET['act'])) {
      switch ($_GET['act']) {
        case 'change_email':
          $email = EditEmail::model()->findByPk($_GET['eid']);

          if ($email && $email->hash == $_GET['hash']) {
            /** @var $user User */
            $user = Yii::app()->user->model;
            $user->email = $email->new_mail;
            $user->save(true, array('email'));

            Yii::import('application.vendors.*');
            require_once 'Mail/Mail.php';

            $mail = Mail::getInstance();
            $mail->setSender(array(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname']));
            $mail->IsMail();

            $html = $this->renderPartial("//mail/report_edit_email", array('email' => $email->new_mail), true);

            $mail->sendMail(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname'], $email->old_mail, 'Смена адреса электронной почты', $html, true, null, null, null);
            $mail->ClearAddresses();

            $report = 'Адрес электронной почты успешно изменен';
          }
          else $err = 'Неверные данные для смены адреса электронной почты. Попробуйте повторить запрос';

          break;
      }
    }

    /** Прямые действия */
    if (isset($_POST['act'])) {
      $result = array();

      switch ($_POST['act']) {
        /* Изменить пароль пользователя */
        case 'changepwd':
          $changepwdmdl->old_password = $_POST['old_password'];
          $changepwdmdl->new_password = $_POST['new_password'];
          $changepwdmdl->rpt_password = $_POST['rpt_password'];

          if ($changepwdmdl->validate()) {
            /** @var $user User */
            $user = Yii::app()->user->model;
            $user->password = $user->hashPassword($changepwdmdl->new_password, $user->salt);
            $user->save(true, array('password'));

            $result['success'] = true;
            $result['message'] = 'Пароль успешно изменен';
          }
          else {
            $errors = array();
            foreach ($changepwdmdl->getErrors() as $attr => $error) {
              $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
            }
            $result['message'] = implode('<br/>', $errors);
          }
          break;
        case 'changeemail':
          $changeemailmdl->new_mail = $_POST['new_mail'];
          if ($changeemailmdl->validate()) {
            $email = new EditEmail();
            $email->date = date("Y-m-d H:i:s");
            $email->old_mail = Yii::app()->user->model->email;
            $email->new_mail = $changeemailmdl->new_mail;
            $email->owner_id = Yii::app()->user->getId();
            $email->ip = ip2long($_SERVER['REMOTE_ADDR']);
            $email->hash = md5($email->ip . $email->owner_id . $email->new_mail . $email->old_mail . $email->date . rand(0, 10));
            $email->save();

            Yii::import('application.vendors.*');
            require_once 'Mail/Mail.php';

            $mail = Mail::getInstance();
            $mail->setSender(array(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname']));
            $mail->IsMail();

            $html = $this->renderPartial("//mail/edit_email", array('id' => $email->edit_id, 'hash' => $email->hash), true);

            $mail->sendMail(Yii::app()->params['noreplymail'], Yii::app()->params['noreplyname'], $email->new_mail, 'Смена адреса электронной почты', $html, true, null, null, null);
            $mail->ClearAddresses();

            $result['success'] = true;
            $result['message'] = 'На адрес '. $email->new_mail .' отправлена ссылка для подтверждения';
          }
          else {
            $errors = array();
            foreach ($changeemailmdl->getErrors() as $attr => $error) {
              $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
            }
            $result['message'] = implode('<br/>', $errors);
          }
          break;
        case 'changephone':
          $changephonemdl = new ChangePhoneForm();

          if (isset($_POST['ChangePhoneForm'])) {
            $changephonemdl->setScenario(($_POST['eid'] == 0) ? 'receive_code' : 'change_phone');
            $changephonemdl->attributes = $_POST['ChangePhoneForm'];

            if ($changephonemdl->validate()) {
              if ($changephonemdl->getScenario() == 'receive_code') {
                $phone = new EditPhone();
                $phone->owner_id = Yii::app()->user->getId();
                $phone->date = date("Y-m-d H:i:s");
                $phone->old_phone = Yii::app()->user->model->profile->phone;
                $phone->new_phone = str_replace('+', '', $changephonemdl->phone);
                $phone->ip = ip2long($_SERVER['REMOTE_ADDR']);
                $phone->code = $phone->generateCode();
                $phone->save();

                $sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
                $sms->SendMessage($phone->new_phone, Yii::app()->params['smsNumber'], 'Код для подтверждения номера: '. $phone->code);

                $result['success'] = true;
                $result['step'] = 1;
                $result['eid'] = $phone->edit_id;
              }
              else {
                $phone = EditPhone::model()->findByPk($_POST['eid']);

                if ($phone && $phone->code == $changephonemdl->code) {
                  /** @var $user User */
                  $user = Yii::app()->user->model;
                  $user->profile->phone = $phone->new_phone;
                  $user->profile->save(true, array('phone'));

                  $result['success'] = true;
                  $result['step'] = 2;
                  $result['msg'] = 'Номер телефона успешно изменен';
                }
                else {
                  $result[ActiveHtml::activeId($changephonemdl, 'code')] = 'Неверный код';
                }
              }
            }
            else {
              foreach ($changephonemdl->getErrors() as $attr => $error) {
                $result[ActiveHtml::activeId($changephonemdl, $attr)] = $error;
              }
            }

            echo json_encode($result);
            exit;
          }

          $result['html'] = $this->renderPartial('editphone_box', array(
            'changephonemdl' => $changephonemdl,
          ), true);
          break;
      }

      echo json_encode($result);
      exit;
    }

    $act_criteria = new CDbCriteria();
    $act_criteria->order = 'act_id DESC';
    $act_criteria->limit = 1;
    $act_criteria->offset = 1;
    $act_criteria->compare('author_id', Yii::app()->user->getId());
    //$activity = Activity::model()->find($act_criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('settings', array(
        'error' => $err,
        'report' => $report,
      ), true);
    }
    else $this->render('settings', array(
      'error' => $err,
      'report' => $report,
    ));
  }

  public function actionNotify() {
    /** @var $notify ProfileNotify */
    $notify = ProfileNotify::model()->findByPk(Yii::app()->user->getId());

    $err = '';
    $report = '';

    /** Прямые действия */
    if (isset($_POST['act'])) {
      $result = array();

      switch ($_POST['act']) {
        case 'emailnotify':
          $notify->attributes = $_POST['ProfileNotify'];

          if ($notify->save()) {
            $result['success'] = true;
            $result['msg'] = 'Изменения успешно сохранены';
          }
          else {
            foreach ($emailnotifymdl->getErrors() as $attr => $error) {
              $result[ActiveHtml::activeId($emailnotifymdl, $attr)] = $error;
            }
          }
          break;
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('notify', array(
        'notify' => $notify,
        'error' => $err,
        'report' => $report,
      ), true);
    }
    else $this->render('notify', array(
      'notify' => $notify,
      'error' => $err,
      'report' => $report,
    ));
  }
}