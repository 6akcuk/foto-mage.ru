<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.10.12
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

class EventsController extends Controller {
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

  public function actionIndex($id = 0) {
    $org = Organization::model()->with('rooms')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр событий данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр событий данной организации');
    }

    // Events array
    $events = array();

    // Org events without room
    $criteria = new CDbCriteria();
    $criteria->compare('org_id', $id);
    $criteria->addCondition('room_id IS NULL');
    $criteria->addCondition('
    (start_time IS NULL AND end_time IS NULL) OR
    (start_time >= NOW()) OR
    (start_time < NOW() AND end_time >= NOW()) OR
    (start_time IS NULL AND end_time >= NOW())
    ');
    $criteria->order = 'start_time ASC';
    //$criteria->limit = 10;
    $events[0] = array('list' => Event::model()->findAll($criteria), 'num' => Event::model()->count($criteria));

    if ($events[0]['num'] == 0) unset($events[0]);

    // Room events
    foreach ($org->rooms as $room) {
      $criteria = new CDbCriteria();
      $criteria->compare('org_id', $id);
      $criteria->compare('room_id', $room->room_id);
      $criteria->addCondition('
      (start_time IS NULL AND end_time IS NULL) OR
      (start_time >= NOW()) OR
      (start_time < NOW() AND end_time >= NOW()) OR
      (start_time IS NULL AND end_time >= NOW())
      ');
      $criteria->order = 'start_time ASC';
      $criteria->limit = 10;

      $events[$room->room_id] = array('list' => Event::model()->findAll($criteria), 'num' => Event::model()->count($criteria));
      if ($events[$room->room_id]['num'] == 0) unset($events[$room->room_id]);
    }

    $eventsNum = Event::model()->count('org_id = :id', array(':id' => $id));

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('index', array(
        'org' => $org,
        'events' => $events,
        'eventsNum' => $eventsNum,
      ), true);
    }
    else $this->render('index', array(
      'org' => $org,
      'events' => $events,
      'eventsNum' => $eventsNum,
    ));
  }

  public function actionAdd($id) {
    $org = Organization::model()->with('rooms')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на добавление события в данную организацию');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на добавление события в данную организацию');
    }

    $event = new Event('add');

    if (isset($_POST['title'])) {
      $event = new Event('add');
      $event->org_id = $id;
      $event->title = $_POST['title'];
      $event->shortstory = $_POST['shortstory'];
      $event->event_type_id = $_POST['event_type_id'];
      $event->room_id = (isset($_POST['room_id']) && $_POST['room_id']) ? $_POST['room_id'] : null;
      $event->price = $_POST['price'];
      $event->weekly = $_POST['weekly'];
      // Работа с часовыми поясами
      $source_tz = new DateTimeZone($event->org->city->timezone);
      $target_tz = new DateTimeZone("Europe/Moscow");

      if (isset($_POST['start_time'])) {
        $start = new DateTime($_POST['start_time'], $source_tz);
        $start->setTimezone($target_tz);
      }
      if (isset($_POST['end_time'])) {
        $end = new DateTime($_POST['end_time'], $source_tz);
        $end->setTimezone($target_tz);
      }

      $event->start_time = (isset($_POST['start_time'])) ? $start->format('Y-m-d H:i:s') : null;
      $event->end_time = (isset($_POST['end_time'])) ? $end->format('Y-m-d H:i:s') : null;
      $event->photo = $_POST['photo'];

      if ($event->save()) {
        $result['success'] = true;
        $result['message'] = 'Событие успешно добавлено';
      }
      else {
        $errors = array();
        foreach ($event->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $eventTypes = EventType::model()->findAll(array('order' => 'type_name'));

    $this->pageHtml = $this->renderPartial('addBox', array(
      'event' => $event,
      'org' => $org,
      'eventTypes' => $eventTypes,
    ), true);
  }

  public function actionEdit($id) {
    /** @var Event $event */
    $event = Event::model()->with('org')->findByPk($id);
    if (!$event)
      throw new CHttpException(404, 'Событие не найдено');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $event->org)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование события в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' =>  $event->org->org_id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на редактирование события в данной организации');
    }

    if (isset($_POST['title'])) {
      $event->title = $_POST['title'];
      $event->shortstory = $_POST['shortstory'];
      $event->event_type_id = $_POST['event_type_id'];
      $event->room_id = (isset($_POST['room_id'])) ? $_POST['room_id'] : null;
      $event->price = $_POST['price'];
      $event->weekly = $_POST['weekly'];
      // Работа с часовыми поясами
      $source_tz = new DateTimeZone($event->org->city->timezone);
      $target_tz = new DateTimeZone("Europe/Moscow");

      if (isset($_POST['start_time'])) {
        $start = new DateTime($_POST['start_time'], $source_tz);
        $start->setTimezone($target_tz);
      }
      if (isset($_POST['end_time'])) {
        $end = new DateTime($_POST['end_time'], $source_tz);
        $end->setTimezone($target_tz);
      }

      $event->start_time = (isset($_POST['start_time'])) ? $start->format('Y-m-d H:i:s') : null;
      $event->end_time = (isset($_POST['end_time'])) ? $end->format('Y-m-d H:i:s') : null;
      $event->photo = $_POST['photo'];

      if ($event->save(true, array('title', 'shortstory', 'event_type_id', 'room_id', 'price', 'start_time', 'end_time', 'photo', 'weekly'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($event->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $eventTypes = EventType::model()->findAll(array('order' => 'type_name'));

    $this->pageHtml = $this->renderPartial('editBox', array(
      'event' => $event,
      'eventTypes' => $eventTypes,
    ), true);
  }

  public function actionDelete($id) {
    $event = Event::model()->findByPk($id);
    if (!$event)
      throw new CHttpException(404, 'Событие не найдено');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $event->org)))
        throw new CHttpException(403, 'У Вас нет прав на удаление события в данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $event->org->org_id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на удаление события в данной организации');
    }

    $event->delete();

    $result['success'] = true;
    $result['message'] = 'Событие успешно удалено';

    echo json_encode($result);
    exit;
  }

  public function actionTypes($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('orgs')->eventTypesPerPage;
    $criteria->offset = $offset;

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('name', $c['name'], true);
    }

    $eventTypes = EventType::model()->findAll($criteria);
    $eventTypesNum = EventType::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_eventtypelist', array('eventTypes' => $eventTypes, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('types', array(
        'eventTypes' => $eventTypes,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $eventTypesNum,
      ), true);
    }
    else $this->render('types', array(
      'eventTypes' => $eventTypes,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $eventTypesNum,
    ));
  }

  public function actionAddType() {
    $type = new EventType('add');

    // collect user input data
    if(isset($_POST['type_name']))
    {
      $type->type_name = $_POST['type_name'];
      $type->type_today = $_POST['type_today'];
      $result = array();

      if($type->save()) {
        $result['success'] = true;
        $result['message'] = 'Тип событий был успешно добавлен';
      }
      else {
        $errors = array();
        foreach ($type->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addTypeBox', array('type' => $type), true);
      }
      else $this->pageHtml = $this->renderPartial('addType', array('type' => $type), true);
    }
    else $this->render('addType', array('type' => $type));
  }

  public function actionEditType($id) {
    $type = EventType::model()->findByPk($id);
    if (!$type)
      throw new CHttpException(404, 'Тип не найден');

    $type->setScenario('edit');

    // collect user input data
    if(isset($_POST['type_name']))
    {
      $type->type_name = $_POST['type_name'];
      $type->type_today = $_POST['type_today'];
      $result = array();

      if($type->save(true, array('type_name', 'type_today'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($type->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editTypeBox', array('type' => $type), true);
      }
      else $this->pageHtml = $this->renderPartial('editType', array('type' => $type), true);
    }
    else $this->render('editType', array('type' => $type));
  }

  public function actionDeleteType($id) {
    $type = EventType::model()->findByPk($id);
    if (!$type)
      throw new CHttpException(404, 'Тип не найден');

    $type->delete();

    echo json_encode(array('message' => 'Тип событий успешно удален'));
    exit;
  }
}