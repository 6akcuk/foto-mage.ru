<?php
/**
 * Created by PhpStorm.
 * User: Sum
 * Date: 20.01.14
 * Time: 20:35
 */

class TaxiDriverController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  /**
   * Получить все сведения о водителе для работы клиента (приложения)
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionCheckDriver($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    $s = new DateTime(urldecode($_POST['sync']), new DateTimeZone($city->timezone));
    $d = new DateTime('now', new DateTimeZone('Europe/Moscow'));
    $d->setTimezone(new DateTimeZone($city->timezone));
    $diff = $d->diff($s);

    $sync = ($diff->s + $diff->i * 60 + $diff->h * 3600); //$d->getTimestamp() - $sync;
    if ($diff->invert == 0) $sync = -$sync;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    if ($driver) {
      $result['driver_id'] = $driver->driver_id;
      $result['rating'] = $driver->rating;
      $result['is_active'] = (strtotime($driver->activated_until) > time());
      $result['active_until'] = ActiveHtml::edate($driver->activated_until, true, false, $city->timezone);
      $result['position'] = ($driver->position) ? $driver->position->area_id : 0;
      if ($driver->position) {
        $result['position_name'] = $driver->position->area->name;
        //$result['position_update'] = $driver->position->lastupdate;
      }

      if ($driver->order) {
        $result['order_id'] = $driver->order->order_id;
        $result['order_data'] = $driver->order->getAPIData();
      } else $result['order_id'] = 0;
    } else $result['driver_id'] = 0;

    $result['sync'] = $sync;

    $this->json = $result;
  }

  /**
   * Зарегистрировать водителя
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionRegisterDriver($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    $check_driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    if ($check_driver)
      throw new CHttpException(500, 'На данного пользователя уже заведен аккаунт водителя');

    $driver = new TaxiDriver();
    $driver->user_id = Yii::app()->user->getId();
    $driver->firstname = $_POST['firstname'];
    $driver->lastname = $_POST['lastname'];
    $driver->middlename = $_POST['middlename'];
    $driver->car_brand = $_POST['car_brand'];
    $driver->car_model = $_POST['car_model'];
    $driver->car_number = $_POST['car_number'];
    $driver->car_color = $_POST['car_color'];

    if ($driver->save()) {
      $result['success'] = true;
      $result['message'] = 'Водитель успешно зарегистрирован';
    } else {
      $errors = array();
      foreach ($driver->getErrors() as $attr => $error) {
        $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
      }
      $result['message'] = implode('<br/>', $errors);
    }

    $this->json = $result;
  }

  /**
   * Редактировать данные водителя
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionEditDriver($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    if (isset($_POST['lastname'])) {
      $driver->firstname = $_POST['firstname'];
      $driver->lastname = $_POST['lastname'];
      $driver->middlename = $_POST['middlename'];
      $driver->car_brand = $_POST['car_brand'];
      $driver->car_model = $_POST['car_model'];
      $driver->car_number = $_POST['car_number'];
      $driver->car_color = $_POST['car_color'];

      if ($driver->save(true, array('firstname', 'lastname', 'middlename', 'car_brand', 'car_model', 'car_number', 'car_color'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      } else {
        $errors = array();
        foreach ($driver->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }
    }
    else {
      $result['lastname'] = $driver->lastname;
      $result['firstname'] = $driver->firstname;
      $result['middlename'] = $driver->middlename;
      $result['car_brand'] = $driver->car_brand;
      $result['car_model'] = $driver->car_model;
      $result['car_number'] = $driver->car_number;
      $result['car_color'] = $driver->car_color;
    }

    $this->json = $result;
  }

  /**
   * Список районов города
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionListAreas($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    $areas = TaxiArea::model()->findAll();

    $areas_stat = array();
    foreach ($areas as $area) {
      $order_criteria = new CDbCriteria();
      $order_criteria->compare('from_area_id', $area->area_id);
      $order_criteria->addInCondition('status', array(TaxiOrder::STATUS_POSTED, TaxiOrder::STATUS_SEARCH, TaxiOrder::STATUS_QUEUE));

      $areas_stat[] = array(
        'area_id' => $area->area_id,
        'name' => $area->name,
        'drivers' => TaxiDriverPosition::model()->count('area_id = :id', array(':id' => $area->area_id)),
        'orders' => TaxiOrder::model()->count($order_criteria),
      );
    }

    $this->json = $areas_stat;
  }

  /**
   * Встать в очередь района
   *
   * @param $city_id
   * @param $area_id
   * @throws CHttpException
   */
  public function actionEnterArea($city_id, $area_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    $area = TaxiArea::model()->findByPk($area_id);

    $transaction = $db->beginTransaction();
    try {
      $queue = TaxiAreaQueue::model()->find('area_id = :id', array(':id' => $area_id));
      if ($queue) $json_queue = json_decode($queue->queue, true);
      else {
        $queue = new TaxiAreaQueue();
        $queue->area_id = $area_id;
        $json_queue = array();
      }

      /** @var TaxiDriver $driver */
      $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
      if (!in_array($driver->driver_id, $json_queue))
        $json_queue[] = $driver->driver_id;

      $criteria = new CDbCriteria();
      $criteria->addInCondition('driver_id', $json_queue);
      $criteria->order = 'rating DESC';

      $drivers = TaxiDriver::model()->findAll($criteria);
      $new_queue = array();
      /** @var TaxiDriver $drv */
      foreach ($drivers as $drv) {
        $new_queue[] = $drv->driver_id;
      }

      LPHelper::send('taxi_c34_a'. $queue->area_id, implode('<!>', $new_queue));

      $queue->queue = json_encode($new_queue);
      $queue->save(true, array('area_id', 'queue'));

      $position = TaxiDriverPosition::model()->findByPk($driver->driver_id);
      if (!$position) {
        $position = new TaxiDriverPosition();
        $position->driver_id = $driver->driver_id;
        $position->area_id = $area_id;
        $position->save();
      }

      $result['success'] = true;
      $result['area_id'] = $area_id;
      $result['area_name'] = $area->name;

      $transaction->commit();
    }
    catch (Exception $e) {
      $transaction->rollback();
      $result['message'] = $e->getMessage();
    }

    $this->json = $result;
  }

  /**
   * Постоянное удержание водителя в сети
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionDriverHeartbeat($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    /** @var TaxiDriverPosition $position */
    $position = TaxiDriverPosition::model()->find('driver_id = :id', array(':id' => $driver->driver_id));
    $position->save(true, array('lastupdate'));
  }

  /**
   * Покинуть район
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionLeaveArea($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    if ($driver->position) {
      $transaction = $db->beginTransaction();
      try {
        $queue = TaxiAreaQueue::model()->find('area_id = :id', array(':id' => $driver->position->area_id));
        $json_queue = json_decode($queue->queue, true);

        array_splice($json_queue, array_search($driver->driver_id, $json_queue), 1);

        LPHelper::send('taxi_c34_a'. $queue->area_id, implode('<!>', $json_queue));

        $queue->queue = json_encode($json_queue);
        $queue->save(true, array('queue'));

        $driver->position->delete();

        $result['success'] = true;

        $transaction->commit();
      }
      catch (Exception $e) {
        $transaction->rollback();
        $result['message'] = $e->getMessage();
      }
    }

    $this->json = $result;
  }

  /**
   * Получить информацию о районе
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionGetAreaInfo($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    $position = TaxiDriverPosition::model()->find('driver_id = :id', array(':id' => $driver->driver_id));
    $queue = TaxiAreaQueue::model()->find('area_id = :id', array(':id' => $position->area_id));
    $area = TaxiArea::model()->findByPk($position->area_id);

    $criteria = new CDbCriteria();
    $criteria->addInCondition('status', array(TaxiOrder::STATUS_POSTED, TaxiOrder::STATUS_SEARCH, TaxiOrder::STATUS_QUEUE));
    $criteria->compare('from_area_id', $position->area_id);

    $queueOrdersNum = TaxiOrder::model()->count($criteria);

    $this->json = array(
      'area_id' => $area->area_id,
      'driver_id' => $driver->driver_id,
      'orders' => $queueOrdersNum,
      'name' => $area->name,
      'queue' => json_decode($queue->queue, true),
    );
  }

  /**
   * Установить стоимость поездки
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionSetOrderPrice($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    if (!isset($_POST['price']))
      throw new CHttpException(500, 'Не передан параметр цены');

    $price = floatval($_POST['price']);
    if ($price <= 0)
      throw new CHttpException(500, 'Цена должна быть больше 0');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    if ($driver->order) {
      if ($driver->order->status == TaxiOrder::STATUS_AUCTION) {
        $driver->order->price = $price;
        $driver->order->save(true, array('price'));

        if ($driver->order->customer->iOSDeviceToken) {
          APNSHelper::send($driver->order->customer->iOSDeviceToken, 'Водитель установил стоимость поездки в '. ActiveHtml::price($price));
        }
        if ($driver->order->customer->AndroidPushToken) {
          GCMHelper::send($driver->order->customer->AndroidPushToken, array(
            'message' => 'Водитель установил стоимость поездки в '. ActiveHtml::price($price),
            'module' => 'taxi',
          ));
        }
      }
    }
  }

  /**
   * Получить информацию о заказе
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionGetOrderInfo($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    if ($driver->order) {
      $result = array(
        'order_id' => $driver->order->order_id,
        'order_data' => $driver->order->getAPIData(),
      );
    } else {
      $result['order_id'] = 0;
    }

    $this->json = $result;
  }

  /**
   * Обновлять статус заказа
   *
   * @param $city_id
   * @param $xstatus
   * @throws CHttpException
   */
  public function actionUpdateOrder($city_id, $xstatus) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    if ($driver->order) {
      if ($xstatus == TaxiOrder::STATUS_ON_THE_WAY) {
        $driver->order->status = TaxiOrder::STATUS_WAITING;
        $driver->order->save(true, array('status'));

        SMSHelper::send(preg_replace("/^8(\d+)/ui", "+7$1", $driver->order->customer_phone), 'К вам подъехало такси: '. $driver->getDriverSMSText());

        //$sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
        /*$sms->SendMessage(
          preg_replace("/^8(\d+)/ui", "+7$1", $driver->order->customer_phone),
          Yii::app()->params['smsNumber'],
          'К вам подъехало такси: '. $driver->getDriverSMSText()
        );*/
      } elseif ($xstatus == TaxiOrder::STATUS_WAITING) {
        $driver->order->status = TaxiOrder::STATUS_PICKED_UP;
        $driver->order->save(true, array('status'));
      } elseif ($xstatus == TaxiOrder::STATUS_PICKED_UP) {
        $driver->order->end_date = date("Y-m-d H:i:s");
        $driver->order->status = TaxiOrder::STATUS_FINISHED;
        $driver->order->save(true, array('status', 'end_date'));
      }
    }
  }

  /**
   * Подтвердить заказ
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionAcceptOrder($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    $transaction = $db->beginTransaction();
    try {
      if ($driver->order && $driver->order->status == TaxiOrder::STATUS_QUEUE) {
        $queue = TaxiAreaQueue::model()->find('area_id = :id', array(':id' => $driver->position->area_id));
        $json_queue = json_decode($queue->queue, true);

        array_splice($json_queue, array_search($driver->driver_id, $json_queue), 1);

        LPHelper::send('taxi_c34_a'. $queue->area_id, implode('<!>', $json_queue));

        $queue->queue = json_encode($json_queue);
        $queue->save(true, array('queue'));

        $driver->position->delete();

        $driver->order->status = ($driver->order->price > 0) ? TaxiOrder::STATUS_ON_THE_WAY : TaxiOrder::STATUS_AUCTION;
        $driver->order->save(true, array('status'));

        TaxiDriverOrderQueue::model()->deleteAll('driver_id = :id AND order_id = :oid', array(':id' => $driver->driver_id, ':oid' => $driver->order->order_id));

        //SMSHelper::send(preg_replace("/^8(\d+)/ui", "+7$1", $driver->order->customer_phone), 'На ваш заказ назначен автомобиль: '. $driver->getDriverSMSText());

        /*$sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
        $sms->SendMessage(
          preg_replace("/^8(\d+)/ui", "+7$1", $driver->order->customer_phone),
          Yii::app()->params['smsNumber'],
          'На ваш заказ назначен автомобиль: '. $driver->getDriverSMSText()
        );*/
        if ($driver->order->customer->iOSDeviceToken) {
          APNSHelper::send($driver->order->customer->iOSDeviceToken, 'Начинается аукцион по Вашему заказу такси');
        }
        if ($driver->order->customer->AndroidPushToken) {
          GCMHelper::send($driver->order->customer->AndroidPushToken, array(
            'message' => 'Начинается аукцион по Вашему заказу такси',
            'module' => 'taxi',
          ));
        }

        $result = array(
          'order_id' => $driver->order->order_id,
          'order_data' => $driver->order->getAPIData(),
        );

        $transaction->commit();
      } else {

      }
    }
    catch (Exception $e) {
      $transaction->rollback();
      $result['message'] = $e->getMessage();
    }

    $this->json = $result;
  }

  /**
   * Отказаться от заказа
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionDeclineOrder($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    /** @var TaxiDriver $driver */
    $driver = TaxiDriver::model()->find('user_id = :id', array(':id' => Yii::app()->user->getId()));
    $transaction = $db->beginTransaction();
    try {
      if ($driver->order) {
        $driver->order->driver_id = null;
        $driver->order->status = TaxiOrder::STATUS_SEARCH;
        $driver->order->price = 0;
        $driver->order->save(true, array('driver_id', 'status', 'price'));

        /** @var TaxiDriverOrderQueue $od_queue */
        $od_queue = TaxiDriverOrderQueue::model()->find('order_id = :oid', array(':oid' => $driver->order->order_id));
        if ($od_queue) {
          $od_queue->queue_time = null;
          $od_queue->save(true, array('queue_time'));
        }
        $driver_queue = TaxiDriverOrderQueue::model()->find('driver_id = :id', array(':id' => $driver->driver_id));
        if (!$driver_queue) {
          $driver_queue = new TaxiDriverOrderQueue();
          $driver_queue->driver_id = $driver->driver_id;
          $driver_queue->queue_data = json_encode(array($driver->order->order_id));
          $driver_queue->save();
        } else {
          $data = json_decode($driver_queue->queue_data, true);
          $data[] = $driver->order->order_id;

          $driver_queue->queue_data = json_encode($data);
          $driver_queue->save(true, array('queue_data'));
        }
        //TaxiDriverOrderQueue::model()->deleteAll('driver_id = :id AND order_id = :oid', array(':id' => $driver->driver_id, ':oid' => $driver->order->order_id));

        if ($driver->order->customer->iOSDeviceToken) {
          APNSHelper::send($driver->order->customer->iOSDeviceToken, 'Водитель отказался от Вашего заказа');
        }
        if ($driver->order->customer->AndroidPushToken) {
          GCMHelper::send($driver->order->customer->AndroidPushToken, array(
            'message' => 'Водитель отказался от Вашего заказа',
            'module' => 'taxi',
          ));
        }

        $transaction->commit();
      }
    }
    catch (Exception $e) {
      $transaction->rollback();
      $result['message'] = $e->getMessage();
    }
  }

}