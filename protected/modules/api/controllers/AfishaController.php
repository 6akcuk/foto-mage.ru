<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.10.12
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

class AfishaController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  public function actionEventAvatar($id) {
    Yii::import('orgs.models.*');

    /** @var Event $event */
    $event = Event::model()->with('event_type')->findByPk($id);
    if (!$event)
      throw new CHttpException(404, 'Событие не найдено');

    $photo = json_decode($event->photo, true);
    $resort = (isset($photo['y'])) ? $photo['y'] : $photo['w'];

    $this->json = array(
      'photo' => ($photo) ? 'http://cs'. $resort[2] .'.'. Yii::app()->params['domain'] .'/'. $resort[0] .'/'. $resort[1] : '',
    );
  }

  public function actionEvent($id) {
    Yii::import('orgs.models.*');

    /** @var Event $event */
    $event = Event::model()->with('org', 'org.city', 'event_type')->findByPk($id);
    if (!$event)
      throw new CHttpException(404, 'Событие не найдено');

    $photo = json_decode($event->photo, true);

    $this->json = array(
      'title' => $event->title,
      'org_id' => $event->org_id,
      'org_name' => $event->org->name,
      'address' => $event->org->address,
      'photo' => ($photo) ? 'http://cs'. $photo['a'][2] .'.'. Yii::app()->params['domain'] .'/'. $photo['a'][0] .'/'. $photo['a'][1] : '',
      'event_type_name' => $event->event_type->type_name,
      'shortstory' => $event->shortstory,
      'price' => ($event->price) ? $event->price : '',
      'start' => ($event->start_time) ? ActiveHtml::date($event->start_time, true, false, false, true, true, $event->org->city->timezone) : '',
      'end' => ($event->end_time) ? ActiveHtml::date($event->end_time, true, false, false, true, true, $event->org->city->timezone) : '',
    );
  }

  /**
   * Информация об организации
   *
   * @param $id
   * @throws CHttpException
   */
  public function actionOrg($id, $density = null) {
    Yii::import('orgs.models.*');

    /** @var Organization $org */
    /** @var Room $room */
    $org = Organization::model()->with('city', 'org_type', 'rooms')->findByPk($id);
    $rooms = array();
    $events = array();

    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    // Org events without room
    $criteria = new CDbCriteria();
    $criteria->compare('org_id', $id);

    $source_tz = new DateTimeZone($org->city->timezone);
    $target_tz = new DateTimeZone('Europe/Moscow');

    $now = new DateTime('now', $source_tz);
    $now->setTimezone($target_tz);

    $criteria->addCondition('room_id IS NULL');
    $criteria->addCondition("
      (
        (start_time IS NULL AND end_time IS NULL) OR
        (start_time >= '". $now->format('Y-m-d H:i:s') ."') OR
        (start_time < '". $now->format('Y-m-d H:i:s') ."' AND end_time >= '". $now->format('Y-m-d H:i:s') ."') OR
        (start_time IS NULL AND end_time >= '". $now->format('Y-m-d H:i:s') ."')
      )
      ");
    $criteria->order = 'start_time ASC';

    $events = array();
    $roomEvents = array();
    $list = Event::model()->findAll($criteria);
    /** @var Event $eve */
    foreach ($list as $eve) {
      $photo = json_decode($eve->photo, true);

      $events[] = array(
        'id' => $eve->event_id,
        'title' => $eve->title,
        'photo' => ($photo) ? 'http://cs'. $photo['b'][2] .'.'. Yii::app()->params['domain'] .'/'. $photo['b'][0] .'/'. $photo['b'][1] : '',
        'price' => $eve->price,
        'start' => ($eve->start_time) ? ActiveHtml::date($eve->start_time, true, false, false, true, true, $org->city->timezone) : ''
      );
    }

    if (sizeof($events))
      $roomEvents[] = array('id' => 0, 'name' => 'Общее', 'events' => $events, 'num' => Event::model()->count($criteria));

    // Room events
    foreach ($org->rooms as $room) {
      $criteria = new CDbCriteria();
      $criteria->compare('org_id', $id);
      $criteria->compare('room_id', $room->room_id);

      $source_tz = new DateTimeZone($org->city->timezone);
      $target_tz = new DateTimeZone('Europe/Moscow');

      $now = new DateTime('now', $source_tz);
      $now->setTimezone($target_tz);

      $criteria->addCondition("
      (
        (start_time IS NULL AND end_time IS NULL) OR
        (start_time >= '". $now->format('Y-m-d H:i:s') ."') OR
        (start_time < '". $now->format('Y-m-d H:i:s') ."' AND end_time >= '". $now->format('Y-m-d H:i:s') ."') OR
        (start_time IS NULL AND end_time >= '". $now->format('Y-m-d H:i:s') ."')
      )
      ");
      $criteria->order = 'start_time ASC';

      $events = array();
      $list = Event::model()->findAll($criteria);
      /** @var Event $eve */
      foreach ($list as $eve) {
        $photo = json_decode($eve->photo, true);

        $events[] = array(
          'id' => $eve->event_id,
          'title' => $eve->title,
          'photo' => ($photo) ? 'http://cs'. $photo['b'][2] .'.'. Yii::app()->params['domain'] .'/'. $photo['b'][0] .'/'. $photo['b'][1] : '',
          'price' => $eve->price,
          'start' => ($eve->start_time) ? ActiveHtml::date($eve->start_time, true, true, false, false, true, $org->city->timezone) : ''
        );
      }

      if (sizeof($events))
        $roomEvents[] = array('id' => $room->room_id, 'name' => $room->name, 'events' => $events, 'num' => Event::model()->count($criteria));
    }

    $eventsNum = Event::model()->count('org_id = :id', array(':id' => $id));
    $photo = json_decode($org->photo, true);

    // Акции
    $criteria = new CDbCriteria();
    $criteria->compare('t.org_id', $id);
    $criteria->compare('t.type', DiscountAction::TYPE_ACTION);
    $criteria->addCondition("
    (
      (start_time IS NULL OR start_time <= '". $now->format('Y-m-d') ."') AND
      (end_time IS NULL OR end_time >= '". $now->format('Y-m-d') ."')
    )
    ");
    $criteria->order = 't.action_id DESC';

    $actions = DiscountAction::model()->with(
      array(
        'hasCode' => array(
          'condition' => (Yii::app()->user->getId() > 0) ? 'owner_id = '. Yii::app()->user->getId() : ''
        )
      )
    )->findAll($criteria);
    $actionsNum = DiscountAction::model()->with(
      array(
        'hasCode' => array(
          'condition' => (Yii::app()->user->getId() > 0) ? 'owner_id = '. Yii::app()->user->getId() : ''
        )
      )
    )->count($criteria);
    $acts = array();

    /** @var DiscountAction $action */
    foreach ($actions as $action) {
      $acts[] = array(
        'id' => $action->action_id,
        'name' => $action->name,
        'photo' => ($action->banner) ? ActiveHtml::getPhotoUrl($action->banner, 'd', $density) : null,
        'haveLimit' => ($action->pc_limits == 0) ? false : true,
        'codesLeft' => Yii::t('app', 'Остался {n} промо-код|Осталось {n} промо-кода|Осталось {n} промо-кодов', $action->pc_limits - $action->cur_pc),
        'have' => false,// ($action->hasCode) ? true : false,
      );
    }

    $this->json = array(
      'name' => $org->name,
      'city' => $org->city->name,
      'org_type_id' => $org->org_type_id,
      'org_type_name' => $org->org_type->type->type_name,
      'shortstory' => $org->shortstory,
      'address' => $org->address,
      'phone' => ActiveHtml::phone($org->phone),
      'photo' => ($org->photo) ? ActiveHtml::getPhotoUrl($org->photo, 'd', $density) : '',
      'worktimes' => $org->worktimes,
      'roomEvents' => $roomEvents,
      'actionsNum' => $actionsNum,
      'actionsNumText' => Yii::t('app', '{n} акция|{n} акции|{n} акций', $actionsNum),
      'actions' => $acts,
    );
  }

  public function actionToday($offset = 0, $city_id = null) {
    Yii::import('orgs.models.*');

    $this->authorize();

    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;

    $city = City::model()->findByPk($city_id);

    $criteria = new CDbCriteria();
    $criteria->compare('event_type.type_today', 1);
    $criteria->compare('org.city_id', $city_id);

    $source_tz = new DateTimeZone($city->timezone);
    $target_tz = new DateTimeZone('Europe/Moscow');

    $start = new DateTime('now', $source_tz);
    $start->setTime(0, 0, 0);
    $start->setTimezone($target_tz);

    $end = new DateTime('now', $source_tz);
    $end->add(DateInterval::createFromDateString('1 day'));
    $end->setTime(6, 59, 59);
    $end->setTimezone($target_tz);

    $now = new DateTime('now', $source_tz);
    $now->setTimezone($target_tz);

    $dows = array(1 => 'Mon','Tue','Wed','Thu','Fri','Sat','Sun');
    $dow = $dows[$now->format('N')];

    $criteria->addCondition("
    (
      (start_time BETWEEN '". $start->format('Y-m-d H:i:s') ."' AND '". $end->format('Y-m-d H:i:s') ."')
      OR
      (end_time BETWEEN '". $now->format('Y-m-d H:i:s') ."' AND '". $end->format('Y-m-d H:i:s') ."')
      OR
      (start_time IS NULL AND end_time IS NULL AND weekly = '')
      OR
      (start_time IS NULL AND end_time IS NULL AND FIND_IN_SET('". $dow ."', weekly) > 0)
    )
    ");
    $criteria->order = 'start_time';
    $criteria->limit = 20;
    $criteria->offset = $offset;

    $events = Event::model()->with('org', 'event_type')->findAll($criteria);
    $offsets = Event::model()->with('org', 'event_type')->count($criteria);

    $items = array();
    /** @var Event $event */
    foreach ($events as $event) {
      $photo = json_decode($event->photo, true);

      $items[] = array(
        'id' => $event->event_id,
        'type' => $event->event_type->type_name,
        'org' => $event->org->name,
        'title' => $event->title,
        'photo' => ($photo) ? 'http://cs'. $photo['c'][2] .'.'. Yii::app()->params['domain'] .'/'. $photo['c'][0] .'/'. $photo['c'][1] : '',
        'price' => $event->price,
        'start' => ($event->start_time) ? 'Начало в '. date("H:i", strtotime($event->start_time)) : ''
      );
    }

    $this->json = array(
      'limit' => 20,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
    );
  }

  /**
   * Список всех организаций
   *
   * @param $id
   * @param int $offset
   */
  public function actionOrgs($id, $offset = 0, $city_id = null) {
    Yii::import('orgs.models.*');

    $this->authorize();

    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('typelink.org_type_id', $id);
    $criteria->compare('city_id', $city_id);
    $criteria->limit = 20;
    $criteria->offset = $offset;

    $orgs = Organization::model()->with('typelink')->findAll($criteria);
    $offsets = Organization::model()->with('typelink')->count($criteria);
    $items = array();
    /** @var Organization $org */
    foreach ($orgs as $org) {
      $items[] = array('id' => $org->org_id, 'name' => $org->name);
    }

    $this->json = array(
      'limit' => 20,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
    );
  }

  /**
   * Список всех типов в Афише
   */
  public function actionTypes() {
    Yii::import('orgs.models.*');

    $types = OrganizationType::model()->findAll('afisha = 1');
    /** @var OrganizationType $type */
    foreach ($types as $type) {
      $this->json[] = array('id' => $type->type_id, 'name' => $type->type_name);
    }
  }
}