<?php

class TaxiClientController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  /**
   * Заказать такси
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionOrder($city_id) {
    Yii::import('taxi.models.*');

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь для оформления заказа');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    // Определение района отправки клиента
    /** @var TaxiAddress $address */
    $address = TaxiAddress::model()->find('street = :street AND house = :house', array(
      ':street' => $_POST['from_street'],
      ':house' => $_POST['from_house'],
    ));
    if (!$address)
      throw new CHttpException(500, 'Невозможно определить район');
    if ($address->area_id != $_POST['from_area_id'])
      throw new CHttpException(500, 'Ошибка синхронизации геоданных');

    $order = new TaxiOrder();
    $order->customer_id = Yii::app()->user->getId();
    $order->customer_phone = Yii::app()->user->model->email;
    $order->from_area_id = $_POST['from_area_id'];
    $order->from_place = $_POST['from_place'];
    $order->from_street = $_POST['from_street'];
    $order->from_house = $_POST['from_house'];
    $order->from_porch = $_POST['from_porch'];
    $order->from_comment = $_POST['from_comment'];
    $order->to_place = $_POST['to_place'];
    //$order->to_street = $_POST['to_street'];
    //$order->to_house = $_POST['to_house'];
    $order->is_deferred = 0;// $_POST['is_deferred'];
    $order->was_reviewed = "0";
    $order->was_voted = "0";
    /*
        $source_tz = new DateTimeZone(Yii::app()->user->model->profile->city->timezone);
        $target_tz = new DateTimeZone("Europe/Moscow");

        if ($_POST['deferred_time']) {
          $def = new DateTime($_POST['deferred_time'], $source_tz);
          $def->setTimezone($target_tz);
        }

        $order->deferred_time = ($_POST['deferred_time']) ? $def->format('Y-m-d H:i:s') : null;
    */
    $result = array();

    if ($order->save()) {
      $result['success'] = true;
      $result['message'] = 'Заявка успешно размещена';
      $result['order_id'] = $order->order_id;
      $result['key'] = $order->poll_key;
      $result['driver'] = $order->getDriver();
      $result['xstatus'] = $order->status;
      $result['status'] = Yii::t('taxi', $order->status);
      $result['from'] = $order->getFrom();
      $result['to'] = $order->getTo();
      $result['price'] = ($order->price > 0) ? ActiveHtml::price($order->price) : 'Не установлена';
      $result['xprice'] = $order->price;
      $result['voted'] = $order->was_voted;
      $result['reviewed'] = $order->was_reviewed;
    } else {
      $errors = array();
      foreach ($order->getErrors() as $attr => $error) {
        $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
      }
      $result['message'] = implode('<br/>', $errors);
    }

    $this->json = $result;
  }

  /**
   * Повторить старый заказ клиента
   *
   * @param $city_id
   * @param $order_id
   * @throws CHttpException
   */
  public function actionRepeatOrder($city_id, $order_id) {
    Yii::import('taxi.models.*');

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь для оформления заказа');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    /** @var TaxiOrder $order */
    $order = TaxiOrder::model()->findByPk($order_id);
    if (!$order)
      throw new CHttpException(404, 'Заказ не найден');
    if ($order->customer_id != Yii::app()->user->getId())
      throw new CHttpException(500, 'Невозможно повторить чужой заказ');

    $new_order = new TaxiOrder();
    $new_order->customer_id = Yii::app()->user->getId();
    $new_order->customer_phone = Yii::app()->user->model->email;
    $new_order->from_area_id = $order->from_area_id;
    $new_order->from_place = $order->from_place;
    $new_order->from_street = $order->from_street;
    $new_order->from_house = $order->from_house;
    $new_order->from_porch = $order->from_porch;
    $new_order->from_comment = $order->from_comment;
    $new_order->to_place = $order->to_place;
    $new_order->to_street = $order->to_street;
    $new_order->to_house = $order->to_house;
    $new_order->was_voted = "0";
    $new_order->was_reviewed = "0";

    $result = array();

    if ($new_order->save()) {
      $result['success'] = true;
      $result['message'] = 'Заявка успешно размещена';
      $result['order_id'] = $new_order->order_id;
      $result['key'] = $new_order->poll_key;
      $result['driver'] = $new_order->getDriver();
      $result['xstatus'] = $new_order->status;
      $result['status'] = Yii::t('taxi', $new_order->status);
      $result['from'] = $new_order->getFrom();
      $result['to'] = $new_order->getTo();
      $result['price'] = ($new_order->price > 0) ? ActiveHtml::price($new_order->price) : 'Не установлена';
      $result['xprice'] = $new_order->price;
      $result['voted'] = $new_order->was_voted;
      $result['reviewed'] = $new_order->was_reviewed;
    } else {
      $errors = array();
      foreach ($order->getErrors() as $attr => $error) {
        $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
      }
      $result['message'] = implode('<br/>', $errors);
    }

    $this->json = $result;
  }

  /**
   * Проверить наличие имеющегося заказа
   *
   * @param $city_id
   */
  public function actionCheckOrder($city_id, $order_id = null) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    if (Yii::app()->user->getIsGuest()) {
      $result['order_id'] = 0;
    } else {
      $criteria = new CDbCriteria();
      $criteria->compare('customer_id', Yii::app()->user->getId());
      $criteria->addNotInCondition('status', array(TaxiOrder::STATUS_FINISHED, TaxiOrder::STATUS_CANCELED));
      /** @var TaxiOrder $order */
      $order = ($order_id) ? TaxiOrder::model()->findByPk($order_id) : TaxiOrder::model()->find($criteria);
      if ($order) {
        if (
          $order->status == TaxiOrder::STATUS_CANCELED ||
          (
            $order->status == TaxiOrder::STATUS_FINISHED &&
            (time() - strtotime($order->end_date)) > 60 * 30
          )
        ) {
          $result['order_id'] = 0;
        } else {
          $result = array(
            'order_id' => $order->order_id,
            'time' => (time() - strtotime($order->start_date)),
            'from' => $order->getFrom(),
            'comment' => $order->from_comment,
            'to' => $order->getTo(),
            'driver' => $order->getDriver(),
            'driver_id' => $order->driver_id,
            'xstatus' => $order->status,
            'status' => Yii::t('taxi', $order->status),
            'price' => ($order->price > 0) ? ActiveHtml::price($order->price) : 'Не установлена',
            'key' => $order->poll_key,
            'xprice' => $order->price,
            'voted' => $order->was_voted,
            'reviewed' => $order->was_reviewed,
          );
        }
      } else $result['order_id'] = 0;
    }

    $this->json = $result;
  }

  /**
   * История заказов клиента
   *
   * @param $city_id
   * @param int $offset
   * @throws CHttpException
   */
  public function actionOrderHistory($city_id, $offset = 0) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Требуется авторизация');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    $criteria = new CDbCriteria();
    $criteria->offset = $offset;
    $criteria->limit = 10;
    $criteria->order = 'order_id DESC';

    $criteria->compare('customer_id', Yii::app()->user->getId());

    $result = array();
    $orders = TaxiOrder::model()->findAll($criteria);
    $offsets = TaxiOrder::model()->count($criteria);
    /** @var TaxiOrder $order */
    foreach ($orders as $order) {
      $result[] = array(
        'order_id' => $order->order_id,
        'date' => ActiveHtml::date($order->start_date),
        'from' => $order->getFrom(),
        'to' => $order->getTo(),
        'driver' => $order->getDriver(),
        'price' => ActiveHtml::price($order->price),
      );
    }

    $this->json = array(
      'limit' => $criteria->limit,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $result,
    );
  }

  /**
   * Поиск всех мест по улице
   *
   * @param $city_id
   * @param int $offset
   * @throws CHttpException
   */
  public function actionGetStreet($city_id, $offset = 0) {
    Yii::import('taxi.models.*');
    $result = array();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    if (!isset($_POST['s']))
      throw new CHttpException(500, 'Не указаны условия поиска');

    $s = trim($_POST['s']);

    $results = array();

    $criteria = new CDbCriteria();
    $criteria->offset = $offset;
    $criteria->limit = 50;
    $criteria->compare('street', $s);
    if (isset($_POST['h']) && $_POST['h']) {
      $criteria->compare('house', intval($_POST['h']));
    }
    $criteria->order = 'house';

    $addresses = TaxiAddress::model()->findAll($criteria);
    $offsets = TaxiAddress::model()->count($criteria);
    /** @var TaxiAddress $address */
    foreach ($addresses as $address) {
      $title = '';
      if ($address->name) {
        $title .= $address->name;
        $title .= " (". $address->street;
        if ($address->house) $title .= ", ". $address->house .")";
        else $title .= ")";
      } else {
        $title .= $address->street;
        if ($address->house) $title .= ", ". $address->house;
      }

      $results[] = array(
        'name' => $title,
        'street' => $address->street,
        'house' => $address->house,
        'place' => $address->name,
        'area_id' => $address->area_id,
      );
    }

    $this->json = array(
      'limit' => $criteria->limit,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $results,
    );
  }

  /**
   * Поиск улиц, заведений по запросу клиента
   *
   * @param $city_id
   * @param int $offset
   * @throws CHttpException
   */
  public function actionSearchPlace($city_id, $offset = 0) {
    Yii::import('taxi.models.*');
    $result = array();

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    if (!isset($_POST['q']))
      throw new CHttpException(500, 'Не указаны условия поиска');

    $q = trim($_POST['q']);
    if (strlen($q) < 3)
      throw new CHttpException(500, 'Короткое условие');

    $results = array();

    if ($offset == 0) {
      $criteria = new CDbCriteria();
      $criteria->condition = "MATCH (street) AGAINST ('$q*' IN BOOLEAN MODE)";
      $criteria->group = 'street';
      $criteria->order = 'street ASC';

      $streets = TaxiAddress::model()->findAll($criteria);
      /** @var TaxiAddress $street */
      foreach ($streets as $street) {
        $results[] = array(
          'name' => $street->street,
          'street' => $street->street,
        );
      }
    }

    $criteria = new CDbCriteria();
    $criteria->offset = $offset;
    $criteria->limit = 50;

    if (preg_match("/[ ]{1}(\d+)$/ui", $q, $house)) {
      $sname = preg_replace("/[ ]{1}". $house[1] ."$/ui", "", $q);
      $house = $house[1];

      $criteria->addCondition("MATCH (name, street) AGAINST ('$sname' IN BOOLEAN MODE)");
      $criteria->compare('house', $house);
    } else {
      $criteria->addCondition("MATCH (name, street) AGAINST ('$q*' IN BOOLEAN MODE)");
    }

    $addresses = TaxiAddress::model()->findAll($criteria);
    $offsets = TaxiAddress::model()->count($criteria);
    /** @var TaxiAddress $address */
    foreach ($addresses as $address) {
      $title = '';
      if ($address->name) {
        $title .= $address->name;
        $title .= " (". $address->street;
        if ($address->house) $title .= ", ". $address->house .")";
        else $title .= ")";
      } else {
        $title .= $address->street;
        if ($address->house) $title .= ", ". $address->house;
      }

      $results[] = array(
        'name' => $title,
        'street' => $address->street,
        'house' => $address->house,
        'place' => $address->name,
        'area_id' => $address->area_id,
      );
    }

    $this->json = array(
      'limit' => $criteria->limit,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $results,
    );
  }

  /**
   * Отменить заказ
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionCancelOrder($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('customer_id', Yii::app()->user->getId());
    $criteria->addNotInCondition('status', array(TaxiOrder::STATUS_FINISHED, TaxiOrder::STATUS_CANCELED));
    /** @var TaxiOrder $order */
    $order = TaxiOrder::model()->find($criteria);
    if ($order) {
      $order->status = TaxiOrder::STATUS_CANCELED;
      $order->end_date = date("Y-m-d H:i:s");
      $order->save(true, array('status', 'end_date'));
    } else $result['success'] = true;

    $this->json = $result;
  }

  /**
   * Согласиться со стоимостью заказа
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionAcceptOrderPrice($city_id) {
    Yii::import('taxi.models.*');

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь для оформления заказа');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('customer_id', Yii::app()->user->getId());
    $criteria->compare('status', TaxiOrder::STATUS_AUCTION);
    /** @var TaxiOrder $order */
    $order = TaxiOrder::model()->find($criteria);
    if ($order) {
      $order->status = TaxiOrder::STATUS_ON_THE_WAY;
      $order->save(true, array('status'));

      SMSHelper::send(preg_replace("/^8(\d+)/ui", "+7$1", $order->customer_phone), 'На ваш заказ назначен автомобиль: '. $order->driver->getDriverSMSText());
    }
  }

  /**
   * Отказаться от стоимости заказа
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionDeclineOrderPrice($city_id) {
    Yii::import('taxi.models.*');

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь для оформления заказа');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('customer_id', Yii::app()->user->getId());
    $criteria->compare('status', TaxiOrder::STATUS_AUCTION);
    /** @var TaxiOrder $order */
    $order = TaxiOrder::model()->find($criteria);
    if ($order) {
      $order->price = 0;
      $order->save(true, array('price'));
    }
  }

  /**
   * Отказаться от водителя
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionDeclineDriver($city_id) {
    Yii::import('taxi.models.*');
    $result = array();

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;
    $db = Yii::app()->db;

    $criteria = new CDbCriteria();
    $criteria->compare('customer_id', Yii::app()->user->getId());
    $criteria->addNotInCondition('status', array(TaxiOrder::STATUS_FINISHED, TaxiOrder::STATUS_CANCELED));
    /** @var TaxiOrder $order */
    $order = TaxiOrder::model()->find($criteria);
    if ($order && $order->driver) {
      $transaction = $db->beginTransaction();
      try {
        $driver_id = $order->driver_id;

        $order->driver_id = null;
        $order->status = TaxiOrder::STATUS_SEARCH;
        $order->price = 0;
        $order->save(true, array('driver_id', 'status', 'price'));

        /** @var TaxiDriverOrderQueue $od_queue */
        $od_queue = TaxiDriverOrderQueue::model()->find('order_id = :oid', array(':oid' => $order->order_id));
        if ($od_queue) {
          $data = json_decode($od_queue->queue_data, true);
          $data[] = $driver_id;

          $od_queue->queue_data = json_encode($data);
          $od_queue->queue_time = null;
          $od_queue->save(true, array('queue_time', 'queue_data'));
        }

        //TaxiDriverOrderQueue::model()->deleteAll('driver_id = :id AND order_id = :oid', array(':id' => $driver->driver_id, ':oid' => $driver->order->order_id));

        $transaction->commit();
      }
      catch (Exception $e) {
        $transaction->rollback();
        $result['message'] = $e->getMessage();
      }
    }
  }

  /**
   * Рейтинг водителю
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionRateDriver($city_id, $order_id) {
    Yii::import('taxi.models.*');

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь для оформления заказа');

    if (!isset($_POST['dir']))
      throw new CHttpException(500, 'Нет направления');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    /*$criteria = new CDbCriteria();
    $criteria->compare('customer_id', Yii::app()->user->getId());
    $criteria->compare('status', TaxiOrder::STATUS_FINISHED);
    $criteria->addCondition('end_date >= NOW() - INTERVAL 2 MINUTE');*/
    /** @var TaxiOrder $order */
    $order = TaxiOrder::model()->findByPk($order_id);
    if ($order && $order->was_voted == 0) {
      /** @var CDbConnection $db */
      $db = Yii::app()->db;
      $t = $db->beginTransaction();

      try {
        $driver = $order->driver;
        if ($_POST['dir'] == 1) $driver->rating += 0.5;
        elseif ($_POST['dir'] == -1) $driver->rating -= 1;

        if ($driver->rating < 0) $driver->rating = 0;
        if ($driver->rating > 10) $driver->rating = 10;

        $order->was_voted = 1;
        $order->save(true, array('was_voted'));

        $driver->save(true, array('rating'));

        $vote = new TaxiDriverVote();
        $vote->author_id = Yii::app()->user->getId();
        $vote->driver_id = $driver->driver_id;
        $vote->rate = ($_POST['dir'] == 1) ? 0.5 : -1;
        $vote->save();

        $t->commit();
      }
      catch (Exception $e) {
        $t->rollback();
      }
    }
  }

  /**
   * Отзыв о водителе
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionReviewDriver($city_id, $order_id) {
    Yii::import('taxi.models.*');

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь для оформления заказа');

    if (!isset($_POST['text']))
      throw new CHttpException(500, 'Нет отзыва');

    $text = trim($_POST['text']);
    if (!$text)
      throw new CHttpException(500, 'Отзыв не должен быть пустым');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    /*$criteria = new CDbCriteria();
    $criteria->compare('customer_id', Yii::app()->user->getId());
    $criteria->compare('status', TaxiOrder::STATUS_FINISHED);
    $criteria->addCondition('end_date >= NOW() - INTERVAL 5 MINUTE');*/
    /** @var TaxiOrder $order */
    $order = TaxiOrder::model()->findByPk($order_id);
    if ($order && $order->was_reviewed == 0) {
      $order->was_reviewed = 1;
      $order->save(true, array('was_reviewed'));

      $review = new TaxiDriverReview();
      $review->driver_id = $order->driver_id;
      $review->author_id = Yii::app()->user->getId();
      $review->text = $text;
      $review->save();
    }
  }

  /**
   * Просмотреть информацию о водителе
   *
   * @param $city_id
   * @throws CHttpException
   */
  public function actionViewDriverInfo($city_id) {
    Yii::import('taxi.models.*');

    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Авторизуйтесь для оформления заказа');

    $city = City::model()->findByPk($city_id);
    Yii::app()->params['taxi_id'] = $city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('customer_id', Yii::app()->user->getId());
    $criteria->addNotInCondition('status', array(TaxiOrder::STATUS_FINISHED, TaxiOrder::STATUS_CANCELED));
    /** @var TaxiOrder $order */
    $order = TaxiOrder::model()->find($criteria);
    if ($order) {
      if ($order->driver) {
        $result['driver_id'] = $order->driver_id;
        $result['name'] = $order->driver->firstname;
        $result['car'] = $order->driver->car_brand .' '. $order->driver->car_model .', '. $order->driver->car_color .', '. $order->driver->car_number;
        $result['rating'] = floor($order->driver->rating);
        $result['reviews_num'] = $order->driver->reviews_count;
        if ($result['reviews_num'] > 0) {
          $result['reviews'] = array();

          $criteria = new CDbCriteria();
          $criteria->compare('driver_id', $order->driver_id);
          $criteria->order = 'date DESC';
          $criteria->limit = 10;

          $reviews = TaxiDriverReview::model()->findAll($criteria);
          /** @var TaxiDriverReview $review */
          foreach ($reviews as $review) {
            $result['reviews'][] = array(
              'author' => $review->author->login,
              'date' => ActiveHtml::date($review->date),
              'text' => $review->text,
            );
          }
        }
      } else $result['driver_id'] = 0;
    } else $result['driver_id'] = 0;

    $this->json = $result;
  }
}