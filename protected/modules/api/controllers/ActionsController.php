<?php

class ActionsController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  public function actionList($offset = 0, $density = null, $city_id = null) {
    Yii::import('orgs.models.*');

    $this->authorize();

    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('org.city_id', $city_id);
    $criteria->compare('t.type', DiscountAction::TYPE_ACTION);

    $city = City::model()->findByPk($city_id);

    $source_tz = new DateTimeZone($city->timezone);
    $target_tz = new DateTimeZone('Europe/Moscow');

    $now = new DateTime('now', $source_tz);
    $now->setTimezone($target_tz);

    $criteria->addCondition("
    (
      (start_time IS NULL OR start_time <= '". $now->format('Y-m-d') ."') AND
      (end_time IS NULL OR end_time >= '". $now->format('Y-m-d') ."')
    )
    ");
    $criteria->offset = $offset;
    $criteria->limit = 10;
    $criteria->order = 't.action_id DESC';

    $actions = DiscountAction::model()->with('org')->findAll($criteria);
    $offsets = DiscountAction::model()->with('org')->count($criteria);

    $items = array();
    /** @var DiscountAction $action */
    foreach ($actions as $action) {
      $items[] = array(
        'id' => $action->action_id,
        'name' => $action->name,
        'banner' => ActiveHtml::getPhotoUrl($action->banner, 'c', $density),
        'org' => $action->org->name,
      );
    }

    $this->json = array(
      'limit' => 10,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
    );
  }

  public function actionMy($offset = 0, $density = null) {
    Yii::import('orgs.models.*');

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Требуется авторизация');

    $criteria = new CDbCriteria();
    if (!Yii::app()->user->getIsGuest()) {
      $criteria->compare('t.owner_id', Yii::app()->user->getId());
    }
    $criteria->compare('t.type', DiscountPromoCode::TYPE_DISCOUNT_CARD);
    $criteria->offset = $offset;
    $criteria->limit = 10;
    $criteria->order = 't.action_id DESC';

    $promos = DiscountPromoCode::model()->with('action')->findAll($criteria);
    $offsets = DiscountPromoCode::model()->with('action')->count($criteria);

    $items = array();
    /** @var DiscountAction $action */
    foreach ($promos as $promo) {
      $action = $promo->action;

      $items[] = array(
        'id' => $action->action_id,
        'name' => $action->name,
        'banner' => ActiveHtml::getPhotoUrl($action->banner, 'c', $density),
        'org' => $action->org->name,
      );
    }

    $this->json = array(
      'limit' => 10,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
    );
  }

  public function actionGetPC($id) {
    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Требуется авторизация');

    Yii::import('orgs.models.*');

    $promo = DiscountPromoCode::model()->find('action_id = :id AND owner_id = :oid', array(':id' => $id, ':oid' => Yii::app()->user->getId()));
    //if ($promo)
    //  throw new CHttpException(500, 'У Вас уже есть промо-код');

    /** @var DiscountAction $action */
    $connection = Yii::app()->db;   // assuming you have configured a "db" connection
    $transaction = $connection->beginTransaction();
    try {
      $action = DiscountAction::model()->findBySql("
                                        SELECT *
                                        FROM discount_actions WHERE action_id = ". $id ."
                                        FOR UPDATE;
                                        ");

      if ($action->cur_pc > 0 && $action->pc_limits > 0 && $action->pc_limits <= $action->cur_pc)
        throw new CHttpException(500, 'Достигнут лимит промо-кодов');

      $promo = new DiscountPromoCode();
      $promo->action_id = $id;
      $promo->org_id = $action->org_id;
      $promo->owner_id = Yii::app()->user->getId();
      $promo->save();

      $action->cur_pc++;
      $action->update();
      $transaction->commit();

      $this->json = array(
        'code' => $promo->value,
      );
    } catch (Exception $e) {
      $transaction->rollBack();

      throw new CHttpException(500, 'Ошибка при формировании кода');
    }
  }

  public function actionShow($id) {
    $this->authorize();

    Yii::import('orgs.models.*');

    /** @var DiscountAction $action */
    $action = DiscountAction::model()->with('org')->findByPk($id);

    if (!$action)
      throw new CHttpException(404, 'Акция не найдена');

    $promo = DiscountPromoCode::model()->find('action_id = :id AND owner_id = :oid', array(':id' => $id, ':oid' => Yii::app()->user->getId()));
    $size = ActiveHtml::getPhotoSize($action->banner, 'd');

    $this->json = array(
      'id' => $action->action_id,
      'name' => $action->name,
      'fullstory' => $action->fullstory,
      'org' => $action->org->name,
      'address' => $action->org->address,
      'banner' => ActiveHtml::getPhotoUrl($action->banner, 'd'),
      'banner_w' => $size[0],
      'banner_h' => $size[1],
      'pc_limits' => ($action->pc_limits) ? Yii::t('app', 'Остался {n} промо-код|Осталось {n} промо-кода|Осталось {n} промо-кодов', $action->pc_limits - $action->cur_pc) : null,
      'code' => ($promo && $promo->type == 1) ? $promo->value : null,//($promo) ? $promo->value : null,
      'isCard' => ($promo && $promo->type == 1) ? true : false,
    );
  }
}