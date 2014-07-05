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

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('events')->eventsPerPage;
    $criteria->offset = $offset;

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('name', $c['name'], true);
    }

    $events = Event::model()->with('event_type')->findAll($criteria);
    $eventsNum = Event::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_eventlist', array('events' => $events, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'events' => $events,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $eventsNum,
      ), true);
    }
    else $this->render('index', array(
      'events' => $events,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $eventsNum,
    ));
  }

  public function actionAdd() {
    $event = new Event('add');

    // collect user input data
    if(isset($_POST['name']))
    {
      $event->event_type_id = $_POST['event_type_id'];
      $event->name = $_POST['name'];
      $event->city_id = $_POST['city_id'];
      $event->address = $_POST['address'];
      $event->worktimes = $_POST['worktimes'];
      $event->shortstory = $_POST['shortstory'];

      $result = array();

      if($event->save()) {
        $result['success'] = true;
        $result['message'] = 'Организация успешно добавлена';
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

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('event' => $event, 'eventTypes' => $eventTypes), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('event' => $event, 'eventTypes' => $eventTypes), true);
    }
    else $this->render('add', array('event' => $event, 'eventTypes' => $eventTypes));
  }

  public function actionEdit($id) {
    $event = Event::model()->findByPk($id);

    // collect user input data
    if(isset($_POST['name']))
    {
      $event->event_type_id = $_POST['event_type_id'];
      $event->name = $_POST['name'];
      $event->city_id = $_POST['city_id'];
      $event->address = $_POST['address'];
      $event->worktimes = $_POST['worktimes'];
      $event->shortstory = $_POST['shortstory'];

      $result = array();

      if($event->save(true, array('event_type_id', 'name', 'city_id', 'address', 'worktimes', 'shortstory'))) {
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

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('event' => $event, 'eventTypes' => $eventTypes), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('event' => $event, 'eventTypes' => $eventTypes), true);
    }
    else $this->render('edit', array('event' => $event, 'eventTypes' => $eventTypes));
  }

  public function actionDelete($id) {
    $event = Event::model()->findByPk($id);
    if (!$event)
      throw new CHttpException(404, 'Организация не найдена');

    $event->delete();

    echo json_encode(array('message' => 'Организация успешно удалена'));
    exit;
  }
}