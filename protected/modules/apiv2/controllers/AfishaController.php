<?php

class AfishaController extends ApiController
{
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
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

  /**
   * Список всех событий
   *
   * @param $id
   * @param int $offset
   */
  public function actionListEvents($type_id, $city_id, $offset = 0, $density = null) {
    Yii::import('orgs.models.*');

    $city = City::model()->findByPk($city_id);

    $criteria = new CDbCriteria();
    $criteria->compare('typelink.org_type_id', $type_id);
    $criteria->compare('city_id', $city_id);
    $criteria->group = 't.title';
    $criteria->limit = 20;
    $criteria->offset = $offset;

    // Search
    if (isset($_POST['q'])) {
      $criteria->addSearchCondition('t.title', $_POST['q']);
    }

    $source_tz = new DateTimeZone($city->timezone);
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

    $events = Event::model()->with('org', 'org.typelink')->findAll($criteria);
    $offsets = Event::model()->with('org', 'org.typelink')->count($criteria);

    $items = array();
    /** @var Event $event */
    foreach ($events as $event) {
      $items[] = array(
        'id' => $event->event_id,
        'title' => $event->title,
        'photo' => ActiveHtml::getPhotoUrl($event->photo, 'b', $density),
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
  public function actionListOrgs($type_id, $city_id, $offset = 0, $density = null) {
    Yii::import('orgs.models.*');

    $criteria = new CDbCriteria();
    $criteria->compare('typelink.org_type_id', $type_id);
    $criteria->compare('city_id', $city_id);
    $criteria->limit = 20;
    $criteria->offset = $offset;

    // Search
    if (isset($_POST['q'])) {
      $criteria->addSearchCondition('t.name', $_POST['q']);
    }

    $orgs = Organization::model()->with('typelink')->findAll($criteria);
    $offsets = Organization::model()->with('typelink')->count($criteria);
    $items = array();
    /** @var Organization $org */
    foreach ($orgs as $org) {
      $items[] = array(
        'id' => $org->org_id,
        'name' => $org->name,
        'photo' => ActiveHtml::getPhotoUrl($org->photo, 'b', $density),
      );
    }

    $this->json = array(
      'limit' => 20,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
    );
  }

  public function actionViewOrg($id, $density = null) {
    Yii::import('orgs.models.*');

    /** @var Organization $org */
    $org = Organization::model()->with('city')->findByPk($id);

    $source_tz = new DateTimeZone('Europe/Moscow');
    $target_tz = new DateTimeZone($org->city->timezone);

    $now = new DateTime('now', $source_tz);
    $now->setTimezone($target_tz);

    $criteria = new CDbCriteria();
    $criteria->compare('t.org_id', $org->org_id);

    $criteria->addCondition("
      (
        (start_time IS NULL AND end_time IS NULL) OR
        (start_time >= '". $now->format('Y-m-d H:i:s') ."') OR
        (start_time < '". $now->format('Y-m-d H:i:s') ."' AND end_time >= '". $now->format('Y-m-d H:i:s') ."') OR
        (start_time IS NULL AND end_time >= '". $now->format('Y-m-d H:i:s') ."')
      )
      ");

    $criteria->group = 't.title';

    $eventsList = array();
    $events = Event::model()->findAll($criteria);
    /** @var Event $event */
    foreach ($events as $event) {
      $timesList = array();

      $tcriteria = new CDbCriteria();
      $tcriteria->select = 't.start_time, t.weekly';
      $tcriteria->compare('t.org_id', $org->org_id);
      $tcriteria->compare('t.title', $event->title);

      $tcriteria->order = 't.start_time';

      $times = Event::model()->findAll($tcriteria);
      /** @var Event $time */
      foreach ($times as $time) {
        $timesList[] = $time->getLStartTime($org->city->timezone);
      }

      $eventsList[] = array(
        'id' => $event->event_id,
        'title' => $event->title,
        'photo' => ActiveHtml::getPhotoUrl($event->photo, 'b', $density),
        'times' => implode("  ", $timesList),
      );
    }

    $this->json = array(
      'id' => $org->org_id,
      'name' => $org->name,
      'shortstory' => $org->shortstory,
      'photo' => ActiveHtml::getPhotoUrl($org->photo, 'e', $density),
      'address' => $org->address,
      'phone' => $org->phone,
      'events' => $eventsList,
    );
  }

  public function actionViewEvent($id, $density = null) {
    Yii::import('orgs.models.*');

    /** @var Event $event */
    $event = Event::model()->with('org', 'org.city')->findByPk($id);

    $source_tz = new DateTimeZone($event->org->city->timezone);
    $target_tz = new DateTimeZone('Europe/Moscow');

    $now = new DateTime('now', $source_tz);
    $now->setTimezone($target_tz);

    $places = array();
    if ($event->event_type_id == 1) {
      $criteria = new CDbCriteria();
      $criteria->compare('events.title', $event->title);
      $criteria->group = 't.org_id';

      $orgs = Organization::model()->with('events')->findAll($criteria);
      /** @var Organization $org */
      foreach ($orgs as $org) {
        $ecriteria = new CDbCriteria();
        $ecriteria->compare('t.org_id', $org->org_id);
        $ecriteria->compare('t.title', $event->title);

        $ecriteria->addCondition("
        (
          (start_time IS NULL AND end_time IS NULL) OR
          (start_time >= '". $now->format('Y-m-d H:i:s') ."') OR
          (start_time < '". $now->format('Y-m-d H:i:s') ."' AND end_time >= '". $now->format('Y-m-d H:i:s') ."') OR
          (start_time IS NULL AND end_time >= '". $now->format('Y-m-d H:i:s') ."')
        )
        ");

        $ecriteria->distinct = true;
        $ecriteria->group = 't.start_time';
        $ecriteria->order = 'start_time ASC';

        $events = Event::model()->with('org', 'org.city')->findAll($ecriteria);

        $times = array();
        foreach ($events as $eve) {
          $times[] = $eve->getLStartTime();
        }

        $places[] = array(
          'id' => $org->org_id,
          'name' => $org->name,
          'times' => array(implode("  ", $times)),
        );
      }
    } else {
      $places[] = array(
        'id' => $event->org_id,
        'name' => $event->org->name,
        'times' => array(
          $event->getLStartTime(),
        ),
      );
    }

    $this->json = array(
      'title' => $event->title,
      'photo' => ActiveHtml::getPhotoUrl($event->photo, 'e', $density),
      'shortstory' => $event->shortstory,
      'places' => $places,
    );
  }
}