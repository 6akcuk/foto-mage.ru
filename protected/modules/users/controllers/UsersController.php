<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 04.10.12
 * Time: 19:08
 * To change this template use File | Settings | File Templates.
 */

class UsersController extends Controller {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
      array(
        'ext.RBACFilter.RBACFilter',
      ),
    );
  }

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('users')->usersPerPage;
    $criteria->offset = $offset;
    $criteria->select = 't.id, t.email, t.login';

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('profile.firstname', $c['name'], true, 'OR');
      $criteria->addSearchCondition('profile.lastname', $c['name'], true, 'OR');
      $criteria->addSearchCondition('t.login', $c['name'], true, 'OR');
      $criteria->addSearchCondition('t.email', $c['name'], true, 'OR');
    }

    if (isset($c['city_id']) && $c['city_id']) {
      $criteria->compare('profile.city_id', $c['city_id']);
    }

    if (isset($c['role']) && $c['role']) {
      $criteria->compare('role.itemname', $c['role']);
    }

    // Ограничение по городу
    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City')) {
      $criteria->compare('profile.city_id', Yii::app()->user->model->profile->city_id);
    }

    if (Yii::app()->user->model->role->itemname != "Администратор") {
      $criteria->addNotInCondition('role.itemname', array('Администратор'));
    }

    $criteria->order = 't.id ASC';

    $users = User::model()->with('role', 'profile', 'profile.city')->findAll($criteria);
    $usersNum = User::model()->with('role', 'profile', 'profile.city')->count($criteria);
    $roles = RbacItem::model()->findAll('type = :type', array(':type' => RbacItem::TYPE_ROLE));

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_userlist', array('users' => $users, 'offset' => $offset), true);
      }
      else
        $this->pageHtml =  $this->renderPartial('index',
          array(
            'users' => $users,
            'roles' => $roles,
            'c' => $c,
            'offset' => $offset,
            'offsets' => $usersNum,
          ), true);
    }
    else $this->render('index', array(
      'users' => $users,
      'roles' => $roles,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $usersNum,
    ));
  }

  public function actionAssignRole() {
    $user_id = intval($_POST['user_id']);
    $role = $_POST['role'];

    /** @var $authMgr IAuthManager */
    $authMgr = Yii::app()->getAuthManager();
    $user = User::model()->with('role', 'profile')->findByPk($user_id);

    if (Yii::app()->user->model->role->itemname != "Администратор" && $user->role->itemname == "Администратор") {
      throw new CHttpException(403, 'У Вас нет прав менять роли администраторов');
    }

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('user' => $user)))
        throw new CHttpException(403, 'У Вас нет прав на изменение роли данному пользователю');

      if ($role == 'Администратор')
        throw new CHttpException(403, 'У Вас нет прав назначать такие высокопривилегированные роли');
    }

    if ($user->role)
      $authMgr->revoke($user->role->itemname, $user_id);

    $authMgr->assign($role, $user_id);

    echo json_encode(array('msg' => 'Изменения сохранены'));
    exit;
  }

  public function actionAddUser() {
    $user = new User('Users.addUser');
    $profile = new Profile('Users.addUser');
    $roles = RbacItem::model()->findAll('type = :type', array(':type' => RbacItem::TYPE_ROLE));

    if (isset($_POST['email'])) {
      $user->email = $_POST['email'];
      $user->login = $_POST['login'];
      $user->salt = $user->generateSalt();
      $user->password = $user->hashPassword($_POST['password'], $user->salt);
      if ($user->validate()) {
        $user->save();

        $profile->user_id = $user->id;
        $profile->city_id =
          (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
            ? Yii::app()->user->model->profile->city_id
            : $_POST['city'];
        $profile->firstname = $_POST['firstname'];
        $profile->lastname = $_POST['lastname'];
        if ($profile->validate()) {
          $profile->save();

          if (Yii::app()->user->model->role->itemname != "Администратор" && $_POST['role'] == "Администратор")
            $_POST['role'] = "Пользователь";

          $authMgr = Yii::app()->getAuthManager();
          $authMgr->assign($_POST['role'], $user->id);

          $result['success'] = true;
          $result['message'] = 'Пользователь успешно добавлен';
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

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addUserBox', array('user' => $user, 'profile' => $profile, 'roles' => $roles), true);
      }
      else $this->pageHtml = $this->renderPartial('addUser', array('user' => $user, 'profile' => $profile, 'roles' => $roles), true);
    }
    else $this->render('addUser', array('user' => $user, 'profile' => $profile, 'roles' => $roles));
  }

  public function actionEditUser($id) {
    $user = User::model()->with('role', 'profile')->findByPk($id);
    if (!$user)
      throw new CHttpException(404, 'Пользователь не найден');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('user' => $user)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование данного пользователя');
    }

    if (Yii::app()->user->model->role->itemname != "Администратор" && $user->role->itemname == "Администратор") {
      throw new CHttpException(403, 'У Вас нет прав редактировать администраторов');
    }

    $roles = RbacItem::model()->findAll('type = :type', array(':type' => RbacItem::TYPE_ROLE));

    if (isset($_POST['email'])) {
      $user->email = $_POST['email'];
      $user->login = $_POST['login'];

      if ($_POST['password'] != $user->password) {
        $user->salt = $user->generateSalt();
        $user->password = $user->hashPassword($_POST['password'], $user->salt);
        $password_changed = true;
      }
      if ($user->validate()) {
        $fields = array('email', 'login');
        if (isset($password_changed)) {
          $fields[] = 'salt';
          $fields[] = 'password';
        }

        $user->save(true, $fields);

        $user->profile->city_id =
          (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
            ? Yii::app()->user->model->profile->city_id
            : $_POST['city'];
        $user->profile->firstname = $_POST['firstname'];
        $user->profile->lastname = $_POST['lastname'];
        if ($user->profile->validate()) {
          $user->profile->save(true, array('city_id', 'firstname', 'lastname'));

          /** @var $authMgr IAuthManager */
          $authMgr = Yii::app()->getAuthManager();
          if ($user->role)
            $authMgr->revoke($user->role->itemname, $user->id);

          if (Yii::app()->user->model->role->itemname != "Администратор" && $_POST['role'] == "Администратор")
            $_POST['role'] = "Пользователь";

          $authMgr = Yii::app()->getAuthManager();
          $authMgr->assign($_POST['role'], $user->id);

          $result['success'] = true;
          $result['message'] = 'Изменения успешно сохранены';
        }
        else {
          $user->delete();

          $errors = array();
          foreach ($user->profile->getErrors() as $attr => $error) {
            $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
          }
          $result['message'] = implode('<br/>', $errors);
        }
      }
      else {
        $errors = array();
        foreach ($user->getErrors() as $attr => $error) {
          $errors[] = $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editUserBox', array('user' => $user, 'profile' => $user->profile, 'roles' => $roles), true);
      }
      else $this->pageHtml = $this->renderPartial('editUser', array('user' => $user, 'profile' => $user->profile, 'roles' => $roles), true);
    }
    else $this->render('editUser', array('user' => $user, 'profile' => $user->profile, 'roles' => $roles));
  }

  public function actionDeleteUser($id) {
    $user = User::model()->with('profile')->findByPk($id);
    if (!$user)
      throw new CHttpException(404, 'Пользователь не найден');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('user' => $user)))
        throw new CHttpException(403, 'У Вас нет прав на удаление данного пользователя');
    }

    /** @var $authMgr IAuthManager */
    $authMgr = Yii::app()->getAuthManager();
    if ($user->role)
      $authMgr->revoke($user->role->itemname, $user->id);

    $user->profile->delete();
    $user->delete();

    $result['message'] = 'Пользователь успешно удален';

    echo json_encode($result);
    exit;
  }

  public function actionDeleteLinkOrg($id, $org_id) {
    $user = User::model()->with('profile')->findByPk($id);
    if (!$user)
      throw new CHttpException(404, 'Пользователь не найден');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('user' => $user)))
        throw new CHttpException(403, 'У Вас нет прав на удаление прикрепленных организаций к данному пользователю');
    }

    $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => $id, ':org_id' => $org_id));
    $link->delete();

    echo json_encode(array());
    exit;
  }

  public function actionLinkOrg($id) {
    $user = User::model()->with('profile')->findByPk($id);
    if (!$user)
      throw new CHttpException(404, 'Пользователь не найден');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('user' => $user)))
        throw new CHttpException(403, 'У Вас нет прав на прикрепление организаций к данному пользователю');
    }

    if (isset($_POST['org_id'])) {
      $link = new UserOrgLink();
      $link->user_id = $id;
      $link->org_id = $_POST['org_id'];
      if ($link->save()) {
        $result['success'] = true;
        $result['message'] = 'Организация успешно закреплена за пользователем';
      }
      else {
        $errors = array();
        foreach ($link->getErrors() as $attr => $error) {
          $errors[] = $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $links = UserOrgLink::model()->with('org')->findAll('user_id = :id', array(':id' => $id));
    $notInclude = array();

    foreach ($links as $link) {
      $notInclude[] = $link->org_id;
    }

    $criteria = new CDbCriteria();
    $criteria->addNotInCondition('org_id', $notInclude);
    $criteria->order = 'name ASC';

    $orgs = Organization::model()->findAll($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('linkOrgBox', array(
          'user' => $user,
          'orgs' => $orgs,
          'links' => $links,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('linkOrg', array(
        'user' => $user,
        'orgs' => $orgs,
        'links' => $links,
      ), true);
    }
    else $this->render('linkOrg', array(
      'user' => $user,
      'orgs' => $orgs,
      'links' => $links,
    ));
  }
}