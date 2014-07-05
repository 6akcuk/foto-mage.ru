<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.10.12
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

class DiscountController extends Controller {
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

  public function actionIndex($id = 0, $offset = 0) {
    /** @var Organization $org */
    $org = Organization::model()->with('modules')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (!$org->modules || ($org->modules && $org->modules->enable_discount == 0))
      throw new CHttpException(500, 'Модуль Дисконтной системы не активирован');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр акций данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр акций данной организации');
    }

    $criteria = new CDbCriteria();
    $criteria->compare('org_id', $id);
    $criteria->offset = $offset;
    $criteria->limit = Yii::app()->getModule('orgs')->discountActionsPerPage;
    $criteria->order = 'action_id DESC';

    $actions = DiscountAction::model()->with('codesNum')->findAll($criteria);
    $actionsNum = DiscountAction::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('index', array(
        'org' => $org,
        'actions' => $actions,
        'offset' => $offset,
        'offsets' => $actionsNum,
      ), true);
    }
    else $this->render('index', array(
      'org' => $org,
      'actions' => $actions,
      'offset' => $offset,
      'offsets' => $actionsNum,
    ));
  }

  public function actionGiveCard($id) {
    $org = Organization::model()->with('modules')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (!$org->modules || ($org->modules && $org->modules->enable_discount == 0))
      throw new CHttpException(500, 'Модуль Дисконтной системы не активирован');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на выдачу карты в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на выдачу карты в данной организации');
    }

    $action = new DiscountAction();

    if (isset($_POST['name'])) {
      $action->name = $_POST['name'];
      $action->banner = $_POST['banner'];
      $action->type = DiscountAction::TYPE_DISCOUNT_CARD;
      $action->org_id = $id;
      $action->pc_limits = 1;
      if ($action->save()) {
        /** @var User $owner */
        $owner = User::model()->find('email = :mailphone', array(':mailphone' => $_POST['owner_id']));

        $promo = new DiscountPromoCode();
        $promo->action_id = $action->action_id;
        $promo->org_id = $id;
        $promo->owner_id = ($owner && $owner->email) ? $owner->id : $_POST['owner_id'];
        $promo->type = DiscountPromoCode::TYPE_DISCOUNT_CARD;
        $promo->value = 13;
        $promo->save();

        if ($promo->save()) {
          $result['success'] = true;
          $result['message'] = 'Карта успешно выдана';
        }
        else {
          $errors = array();
          foreach ($promo->getErrors() as $attr => $error) {
            $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
          }
          $result['message'] = implode('<br/>', $errors);
        }
      }
      else {
        $errors = array();
        foreach ($action->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $this->pageHtml = $this->renderPartial('giveCardBox', array(
      'action' => $action,
      'org' => $org,
    ), true);
  }

  public function actionDeleteCard($id) {
    $action = DiscountAction::model()->with('org')->findByPk($id);
    if (!$action)
      throw new CHttpException(404, 'Карта не найдена');

    if (!$action->org->modules || ($action->org->modules && $action->org->modules->enable_discount == 0))
      throw new CHttpException(500, 'Модуль Дисконтной системы не активирован');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $action->org)))
        throw new CHttpException(403, 'У Вас нет прав на удаление карты в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $action->org->org_id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на удаление карты в данной организации');
    }

    $action->performDelete();

    $result['success'] = true;
    $result['message'] = 'Акция успешно удалена';

    echo json_encode($result);
    exit;
  }

  public function actionAddAction($id) {
    $org = Organization::model()->with('modules')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (!$org->modules || ($org->modules && $org->modules->enable_discount == 0))
      throw new CHttpException(500, 'Модуль Дисконтной системы не активирован');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на добавление акции в данную организацию');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на добавление акции в данную организацию');
    }

    $action = new DiscountAction();

    if (isset($_POST['name'])) {
      $action->org_id = $id;
      $action->name = $_POST['name'];
      $action->fullstory = $_POST['fullstory'];
      $action->banner = $_POST['banner'];
      $action->pc_limits = $_POST['pc_limits'];
      $action->start_time = (isset($_POST['start_time'])) ? $_POST['start_time'] : null;
      $action->end_time = (isset($_POST['end_time'])) ? $_POST['end_time'] : null;

      if ($action->save()) {
        $result['success'] = true;
        $result['message'] = 'Акция успешно добавлена';
      }
      else {
        $errors = array();
        foreach ($action->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $this->pageHtml = $this->renderPartial('addActionBox', array(
      'action' => $action,
      'org' => $org,
    ), true);
  }

  public function actionEditAction($id) {
    /** @var DiscountAction $action */
    $action = DiscountAction::model()->with('org', 'org.modules')->findByPk($id);
    if (!$action)
      throw new CHttpException(404, 'Акция не найдена');

    if (!$action->org->modules || ($action->org->modules && $action->org->modules->enable_discount == 0))
      throw new CHttpException(500, 'Модуль Дисконтной системы не активирован');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $action->org)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование акций в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' =>  $action->org->org_id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на редактирование акций в данной организации');
    }

    if (isset($_POST['name'])) {
      $action->name = $_POST['name'];
      $action->fullstory = $_POST['fullstory'];
      $action->banner = $_POST['banner'];
      $action->pc_limits = $_POST['pc_limits'];
      $action->start_time = (isset($_POST['start_time'])) ? $_POST['start_time'] : null;
      $action->end_time = (isset($_POST['end_time'])) ? $_POST['end_time'] : null;

      if ($action->save(true, array('name', 'fullstory', 'banner', 'pc_limits', 'start_time', 'end_time'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($action->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $this->pageHtml = $this->renderPartial('editActionBox', array(
      'action' => $action,
      'org' => $action->org,
    ), true);
  }

  public function actionDeleteAction($id) {
    $action = DiscountAction::model()->with('org')->findByPk($id);
    if (!$action)
      throw new CHttpException(404, 'Акция не найдена');

    if (!$action->org->modules || ($action->org->modules && $action->org->modules->enable_discount == 0))
      throw new CHttpException(500, 'Модуль Дисконтной системы не активирован');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $action->org)))
        throw new CHttpException(403, 'У Вас нет прав на удаление акции в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $action->org->org_id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на удаление акции в данной организации');
    }

    $action->performDelete();

    $result['success'] = true;
    $result['message'] = 'Акция успешно удалена';

    echo json_encode($result);
    exit;
  }

  public function actionCodes($id, $offset = 0) {
    $action = DiscountAction::model()->with('org')->findByPk($id);
    if (!$action)
      throw new CHttpException(404, 'Акция не найдена');

    if (!$action->org->modules || ($action->org->modules && $action->org->modules->enable_discount == 0))
      throw new CHttpException(500, 'Модуль Дисконтной системы не активирован');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $action->org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр промо-кодов акции в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $action->org->org_id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр промо-кодов акции в данной организации');
    }

    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('orgs')->discountPromoCodesPerPage;
    $criteria->offset = $offset;
    $criteria->order = 'add_date DESC';

    if (isset($c['value']) && $c['value']) {
      $criteria->addSearchCondition('t.value', $c['value'], true);
    }

    $criteria->compare('action_id', $id);

    $codes = DiscountPromoCode::model()->with('owner', 'owner.profile')->findAll($criteria);
    $codesNum = DiscountPromoCode::model()->with('owner', 'owner.profile')->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('codes', array(
        'org' => $action->org,
        'codes' => $codes,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $codesNum,
      ), true);
    }
    else $this->render('codes', array(
      'org' => $action->org,
      'codes' => $codes,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $codesNum,
    ));
  }
}