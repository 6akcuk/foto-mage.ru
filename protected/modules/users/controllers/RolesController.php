<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.10.12
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

class RolesController extends Controller {
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
    $criteria->limit = Yii::app()->getModule('users')->rolesPerPage;
    $criteria->offset = $offset;
    $criteria->condition = 'type = :type';
    $criteria->params = array(':type' => RbacItem::TYPE_ROLE);

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('name', $c['name'], true);
    }

    $roles = RbacItem::model()->findAll($criteria);
    $rolesNum = RbacItem::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_rolelist', array('roles' => $roles, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'roles' => $roles,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $rolesNum,
      ), true);
    }
    else $this->render('index', array(
      'roles' => $roles,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $rolesNum,
    ));
  }

  public function actionCreateRole() {
    $model = new RoleForm();

    // collect user input data
    if(isset($_POST['RoleForm']))
    {
      $model->attributes = $_POST['RoleForm'];
      $result = array();

      if($model->validate()) {
        /** @var $authManager IAuthManager */
        $authManager = Yii::app()->getAuthManager();
        $item = $authManager->getAuthItem($model->name);

        if (!$item) {
          $authManager->createAuthItem($model->name, RbacItem::TYPE_ROLE, $model->description, $model->bizrule);
          $result['success'] = true;
          $result['message'] = 'Роль '. $model->name .' успешно добавлена';
        }
        else {
          $result['message'] = 'Роль с таким именем уже существует';
        }
      }
      else {
        $errors = array();
        foreach ($model->getErrors() as $attr => $error) {
          $errors[] = $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('createRoleBox', array('model' => $model), true);
      }
      else $this->pageHtml = $this->renderPartial('createRole', array('model' => $model), true);
    }
    else $this->render('createRole', array('model' => $model));
  }

  public function actionEditRole($role) {
    /** @var $authManager IAuthManager */
    $authManager = Yii::app()->getAuthManager();
    $item = $authManager->getAuthItem($role);

    $model = new RoleForm();
    $model->bizrule = $item->bizRule;
    $model->description = $item->description;
    $model->name = $item->name;

    // collect user input data
    if(isset($_POST['RoleForm']))
    {
      $model->attributes = $_POST['RoleForm'];
      $result = array();

      if($model->validate()) {
        $old_name = ($item->name != $model->name) ? $item->name : null;
        $item->name = $model->name;
        $item->description = $model->description;
        $item->bizRule = $model->bizrule;

        $authManager->saveAuthItem($item, $old_name);
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($model->getErrors() as $attr => $error) {
          $errors[] = $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editRoleBox', array('model' => $model), true);
      }
      else $this->pageHtml = $this->renderPartial('createRole', array('model' => $model), true);
    }
    else $this->render('createRole', array('model' => $model));
  }

  public function actionDeleteRole($role) {
    /** @var $authManager IAuthManager */
    $authManager = Yii::app()->getAuthManager();
    if (!$authManager->removeAuthItem($role))
      throw new CHttpException(500, 'Элемент отсутствует либо не удалось удалить');

    echo json_encode(array('msg' => 'Роль успешно удалена'));
    exit;
  }

  public function actionOperations($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('users')->operationsPerPage;
    $criteria->offset = $offset;
    $criteria->condition = 'type = :type';
    $criteria->params = array(':type' => RbacItem::TYPE_OPERATION);

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('name', $c['name'], true);
    }

    $operations = RbacItem::model()->findAll($criteria);
    $operationsNum = RbacItem::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_operationlist', array('operations' => $operations, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('operations', array(
        'operations' => $operations,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $operationsNum,
      ), true);
    }
    else $this->render('operations', array(
      'operations' => $operations,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $operationsNum,
    ));
  }

  public function actionCreateOperation() {
    $model = new OperationForm();

    // collect user input data
    if(isset($_POST['OperationForm']))
    {
      $model->attributes=$_POST['OperationForm'];
      $result = array();

      if($model->validate()) {
        /** @var $authManager IAuthManager */
        $authManager = Yii::app()->getAuthManager();
        $item = $authManager->getAuthItem($model->name);

        if (!$item) {
          $authManager->createAuthItem($model->name, RbacItem::TYPE_OPERATION, $model->description, $model->bizrule);
          $result['success'] = true;
          $result['message'] = 'Операция '. $model->name .' успешно добавлена';
        }
        else {
          $result['message'] = 'Операция с таким кодом уже существует';
        }
      }
      else {
        $errors = array();
        foreach ($model->getErrors() as $attr => $error) {
          $errors[] = $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('createOperationBox', array('model' => $model), true);
      }
      else $this->pageHtml = $this->renderPartial('createOperation', array('model' => $model), true);
    }
    else $this->render('createOperation', array('model' => $model));
  }

  public function actionEditOperation($op) {
    /** @var $authManager IAuthManager */
    $authManager = Yii::app()->getAuthManager();
    $item = $authManager->getAuthItem($op);

    $model = new OperationForm();
    $model->bizrule = $item->bizRule;
    $model->description = $item->description;
    $model->name = $item->name;

    // collect user input data
    if(isset($_POST['OperationForm']))
    {
      $model->attributes = $_POST['OperationForm'];
      $result = array();

      if($model->validate()) {
        $old_name = ($item->name != $model->name) ? $item->name : null;
        $item->name = $model->name;
        $item->description = $model->description;
        $item->bizRule = $model->bizrule;

        $authManager->saveAuthItem($item, $old_name);
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($model->getErrors() as $attr => $error) {
          $errors[] = $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editOperationBox', array('model' => $model), true);
      }
      else $this->pageHtml = $this->renderPartial('createRole', array('model' => $model), true);
    }
    else $this->render('createRole', array('model' => $model));
  }

  public function actionDeleteOperation($op) {
    /** @var $authManager IAuthManager */
    $authManager = Yii::app()->getAuthManager();
    if (!$authManager->removeAuthItem($op))
      throw new CHttpException(500, 'Элемент отсутствует либо не удалось удалить');

    echo json_encode(array('msg' => 'Операция успешно удалена'));
    exit;
  }

  public function actionConnect() {
    $roles = RbacItem::model()->findAll('type = :type', array(':type' => RbacItem::TYPE_ROLE));

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('connect', array(
        'roles' => $roles,
      ), true);
    }
    else $this->render('connect', array(
      'roles' => $roles,
    ));
  }

  public function actionLink($role) {
    $operations = RbacItem::model()->findAll('type = :type', array(':type' => RbacItem::TYPE_OPERATION), array('order' => 'name'));
    $childs = RbacItemChild::model()->findAll('parent = :parent', array(':parent' => $role));

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('linkBox', array(
          'operations' => $operations,
          'childs' => $childs,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('link', array(
        'operations' => $operations,
        'childs' => $childs,
      ), true);
    }
    else $this->render('link', array(
      'operations' => $operations,
      'childs' => $childs,
    ));
  }

  public function actionSyncItems($role) {
    $childs = RbacItemChild::model()->findAll('parent = :parent', array(':parent' => $role));
    $items = (isset($_POST['items'])) ? $_POST['items'] : array();

    /** @var $auth IAuthManager */
    $auth = Yii::app()->getAuthManager();

    // Поиск удаленных элементов
    /** @var RbacItemChild $child */
    foreach ($childs as $child) {
      $founded = false;
      foreach ($items as $item => $null) {
        if ($item == $child->child) {
          $founded = true;
          unset($items[$item]);
          break;
        }
      }

      if (!$founded) $auth->removeItemChild($role, $child->child);
    }

    // Добавить новые
    foreach ($items as $item => $null) {
      $auth->addItemChild($role, $item);
    }

    echo json_encode(array('msg' => 'Изменения сохранены'));
    exit;
  }

    public function actionSyncRoleItems() {
        /** @var $child RbacItemChild */
        $role = $_POST['role'];
        $items = (isset($_POST['items'])) ? $_POST['items'] : array();
        $childs = array();
        $roleChilds = RbacItemChild::model()->findAll('parent = :parent', array(':parent' => $role));
        $changed = false;

        /** @var $auth IAuthManager */
        /*$auth = Yii::app()->getAuthManager();

        foreach ($roleChilds as $child) {
            if (!in_array($child->child, $items)) {
                $auth->removeItemChild($role, $child->child);
                $changed = true;
            }
            $childs[] = $child->child;
        }
        foreach ($items as $item) {
            if (!in_array($item, $childs) && $item) {
                $auth->addItemChild($role, $item);
                $changed = true;
            }
        }
*/
        if ($changed) echo json_encode(array('msg' => 'Изменения сохранены'));
        else echo json_encode(array());
        exit;
    }
}