<?php

class TaxiSterlitamakCommand extends CConsoleCommand {
  static function isRunningProcess($process) {
    exec( 'ps axw | grep -v grep | grep '.escapeshellarg($process) .'', $output);
    $enabled = false;

    //var_dump($output);

    foreach($output as $process_line) {
      echo $process_line ."\n";

      if    (preg_match("/^[ ]*(\d+)/i", $process_line))    {
        if($enabled){
          return TRUE;
        }
        $enabled = true;
      }
    }
    return FALSE;
  }

  public function actionDrivers() {
    set_time_limit(0);

    if(!self::isRunningProcess("taxisterlitamak drivers")){
      /** @var CDbConnection $db */
      $db = Yii::app()->db;
      Yii::app()->params['taxi_id'] = 34;
      $taxiDb = Yii::app()->taxi34;

      Yii::import('taxi.models.*');

      while(TRUE) {
        $areas = TaxiArea::model()->findAll();
        $area_ids = array();
        /** @var TaxiArea $area */
        foreach ($areas as $area) {
          $area_ids[] = $area->area_id;
        }

        $transaction = $taxiDb->beginTransaction();
        try {
          $queues = TaxiAreaQueue::model()->findAllBySql("SELECT * FROM `area_queue` WHERE area_id IN (". implode(", ", $area_ids) .") FOR UPDATE");
          /** @var TaxiAreaQueue $queue */
          foreach ($queues as $queue) {
            $json = json_decode($queue->queue, true);
            $area_queue = $json;

            $criteria = new CDbCriteria();
            $criteria->addInCondition('driver_id', $json);

            /** @var TaxiDriverPosition $position */
            $positions = TaxiDriverPosition::model()->findAll($criteria);
            foreach ($positions as $position) {
              if (strtotime($position->lastupdate) < (time() - 5 * 60)) {
                array_splice($area_queue, array_search($position->driver_id, $area_queue), 1);
                $position->delete();
              }

              array_splice($json, array_search($position->driver_id, $json), 1);
            }

            // В очереди есть водители оффлайн
            if (sizeof($json) > 0) {
              foreach ($json as $js_on) {
                array_splice($area_queue, array_search($js_on, $area_queue), 1);
              }
            }

            if (sizeof($area_queue) > 0)
              LPHelper::send('taxi_c34_a'. $queue->area_id, implode('<!>', $area_queue));

            $queue->queue = json_encode($area_queue);
            $queue->save(true, array('queue'));
          }

          /** @var TaxiDriverPosition $position */
          $criteria = new CDbCriteria();
          $criteria->addInCondition('area_id', $area_ids);
          $criteria->addCondition('lastupdate < NOW() - INTERVAL 5 MINUTE');

          //TaxiDriverPosition::model()->deleteAll("area_id IN (". implode(", ", $area_ids) .") AND lastupdate < NOW() - INTERVAL 5 MINUTE");

          $delete = $taxiDb->createCommand("DELETE FROM `drivers_positions`
            WHERE area_id IN (". implode(", ", $area_ids) .") AND lastupdate < NOW() - INTERVAL 5 MINUTE");
          $delete->query();

          $transaction->commit();
        } catch (Exception $e) {
          $transaction->rollback();

          //TODO: Убрать перед запуском в эксплуатацию
          exit($e->getMessage());
        }

        sleep(21);
      }
    } else {
      echo "Error. Taxi drivers is running\n";
    }
  }

  public function actionOrders() {
    set_time_limit(0);

    if(!self::isRunningProcess("taxisterlitamak orders")){
      /** @var CDbConnection $db */
      $db = Yii::app()->db;
      Yii::app()->params['taxi_id'] = 34;
      $taxiDb = Yii::app()->taxi34;

      $city = City::model()->findByPk(34);

      Yii::import('taxi.models.*');
      Yii::import('ext.*');

      while(TRUE) {
        $areas = TaxiArea::model()->findAll();
        $area_ids = array();
        /** @var TaxiArea $area */
        foreach ($areas as $area) {
          $area_ids[] = $area->area_id;
        }

        $source_tz = new DateTimeZone("Europe/Moscow");
        $target_tz = new DateTimeZone($city->timezone);

        $now = new DateTime('now', $source_tz);
        $now->setTimezone($target_tz);

        $where = array();
        $where[] = "t.from_area_id IN (". implode(", ", $area_ids) .")";
        $where[] = "t.status IN ('". TaxiOrder::STATUS_POSTED ."', '". TaxiOrder::STATUS_SEARCH ."', '". TaxiOrder::STATUS_QUEUE ."')";
        $where[] = "(t.is_deferred = 0 OR (t.is_deferred AND t.deferred_time <= '". $now->format('Y-m-d H:i:s') ."'))";

        $criteria = new CDbCriteria();
        $criteria->select = 't.from_area_id, COUNT(*) AS num';
        $criteria->addInCondition('t.from_area_id', $area_ids);
        $criteria->addInCondition('t.status', array(TaxiOrder::STATUS_POSTED, TaxiOrder::STATUS_SEARCH, TaxiOrder::STATUS_QUEUE));
        $criteria->addCondition("t.is_deferred = 0 OR (t.is_deferred AND t.deferred_time <= '". $now->format('Y-m-d H:i:s') ."')");
        $criteria->group = 't.from_area_id';

        $area_orders = array();
        $totalOrders = 0;
        $ordersNum = TaxiOrder::model()->findAll($criteria);
        foreach ($ordersNum as $orderNum) {
          $totalOrders += $orderNum->num;
          $area_orders[$orderNum->from_area_id] = $orderNum->num;
        }

        $command = $taxiDb->createCommand("SELECT * FROM `orders` AS t
          WHERE ". implode(" AND ", $where));
        $reader = $command->query();

        $area_queues = array();
        $do_queues = array(); // driver <-> order queue
        $driver_queues = array();
        $do_cache = array(); // driver <-> order cache
        $od_cache = array(); // order <-> driver cache

        /*echo "Найдено заявок: ". $totalOrders ."\n";
        echo "--------\n";
        echo "Начало транзакции..\n";*/

        $transaction = $taxiDb->beginTransaction();
        try {
          /** @var TaxiAreaQueue $queue */
          $queues = TaxiAreaQueue::model()->findAllBySql("SELECT * FROM `area_queue` WHERE area_id IN (". implode(", ", $area_ids) .") FOR UPDATE");
          foreach ($queues as $queue) {
            $area_queues[$queue->area_id] = json_decode($queue->queue, true);

            // Соберем информацию о водителях, которые ожидают принятия заявок
            $criteria = new CDbCriteria();
            $criteria->addInCondition('driver_id', $area_queues[$queue->area_id]);
            //$criteria->addCondition('queue_time < NOW() - INTERVAL 20 SECOND');

            /** @var TaxiDriverOrderQueue $queue_2 */
            $queues_2 = TaxiDriverOrderQueue::model()->findAll($criteria);
            foreach ($queues_2 as $queue_2) {
              $driver_queues[$queue_2->driver_id] = $queue_2;

              if (time() - strtotime($queue_2->queue_time) <= 40) $do_queues[$queue_2->driver_id] = true;
            }
          }

          //echo "Собрана информация об очередях с ". Yii::t('app', '{n}-го района|{n}-х районов|{n}-ти районов', sizeof($area_queues)) ."\n";
          //echo "\n";

          // Цикл обработки заявок
          while (($row = $reader->read()) !== false) {
            //echo "Заявка №". $row['order_id'] .":\n";

            if (!in_array($row['status'], array(TaxiOrder::STATUS_SEARCH, TaxiOrder::STATUS_QUEUE, TaxiOrder::STATUS_POSTED))) {
              //echo "Выполняемая заявка по ошибке попала\n";
              continue;
            }

            $is_order_new = false;
            $queue_search_idx = -1;

            // Переводим новую заявку в статус поиска водителя
            if ($row['status'] == TaxiOrder::STATUS_POSTED) {
              $update = $taxiDb->createCommand("UPDATE `orders` SET status = '". TaxiOrder::STATUS_SEARCH ."' WHERE order_id = ". $row['order_id']);
              $update->query();

              $lp_channel_text = array(
                $row['order_id'],
                'Не назначен',
                '',
                'Не установлена',
                TaxiOrder::STATUS_SEARCH,
                Yii::t('taxi', TaxiOrder::STATUS_SEARCH),
                $row['poll_key'],
                $row['driver_id'],
                $row['price'],
              );

              LPHelper::send('taxi_order'. $row['order_id'] .'_'. $row['poll_key'], implode('<!>', $lp_channel_text));
              $is_order_new = true;

              //echo "Переведена в статус поиска\n";
            }

            // Если заявка висит у водителя для принятия, ожидаем 40 секунд, потом передаем дальше
            // Новые заявки не могут быть в этом списке, поэтому пропускаем
            if (!$is_order_new) {
              /** @var TaxiDriverOrderQueue $od_queue */
              $od_queue = TaxiDriverOrderQueue::model()->find('order_id = :id', array(':id' => $row['order_id']));
              if ($od_queue) {
                if (time() - strtotime($od_queue->queue_time) <= 40) {
                  //echo "Заявка ожидает подтверждения водителя. Осталось ". (40 - (time() - strtotime($od_queue->queue_time))) ." с.\n";
                  continue;
                }
                else {
                  //echo "Заявка получает индекс смещения ". $od_queue->queue_idx ."\n";
                  $queue_search_idx = $od_queue->queue_idx;

                  // Если у заявки был водитель, необходимо запомнить эту заявку на время
                  if ($row['driver_id'] > 0) {
                    //echo "У данной заявки имеется водитель, переместим ее в кэш\n";

                    if (isset($driver_queues[$row['driver_id']])) {
                      $queue_data = json_decode($driver_queues[$row['driver_id']]->queue_data, true);
                      if (!in_array($row['order_id'], $queue_data)) {
                        $queue_data[] = $row['order_id'];

                        $field_updates = array('queue_data');
                        // если заявка одна на районе и один водитель, дать паузу
                        if ($area_orders[$row['from_area_id']] == 1 && sizeof($area_queues[$row['from_area_id']]) == 1) {
                          $field_updates[] = 'queue_time';
                          $driver_queues[$row['driver_id']]->queue_time = date("Y-m-d H:i:s");
                          $do_queues[$row['driver_id']] = 1;
                        }

                        $driver_queues[$row['driver_id']]->queue_data = json_encode($queue_data);
                        $driver_queues[$row['driver_id']]->save(true, $field_updates);
                      }
                    }
                  }
                  //$queue_search_idx = ($od_queue->driver_id > 0) ? $od_queue->queue_idx : -1;
                  //$od_queue->delete();
                }
              }
            }

            $queue_search_idx++;

            // Если нет водителей, переключим заявку в статус Поиска и продолжим обработку других заявок
            if (!isset($area_queues[$row['from_area_id']]) || sizeof($area_queues[$row['from_area_id']]) == 0) {
              if ($row['status'] == TaxiOrder::STATUS_QUEUE) {
                $update = $taxiDb->createCommand("UPDATE `orders` SET driver_id = 0, status = '". TaxiOrder::STATUS_SEARCH ."' WHERE order_id = ". $row['order_id']);
                $update->query();

                $delete = $taxiDb->createCommand("DELETE FROM `driver_order_queue` WHERE order_id = ". $row['order_id']);
                $delete->query();

                $lp_channel_text = array(
                  $row['order_id'],
                  'Не назначен',
                  '',
                  'Не установлена',
                  TaxiOrder::STATUS_SEARCH,
                  Yii::t('taxi', TaxiOrder::STATUS_SEARCH),
                  $row['poll_key'],
                  $row['driver_id'],
                  $row['price'],
                );

                LPHelper::send('taxi_order'. $row['order_id'] .'_'. $row['poll_key'], implode('<!>', $lp_channel_text));

                //echo "Изменение статуса на Поиск\n";
              }
              //echo "Водители в районе не найдены, пропуск\n";
              //echo "\n";
              continue;
            }

            // запускаем цикл выдачи заявки водителю
            if ($queue_search_idx >= sizeof($area_queues[$row['from_area_id']]))
              $queue_search_idx = 0;

            //echo "Задаем поиск водителя по циклу, начиная с индекса ". $queue_search_idx ."\n";

            $order_updated = false;
            for ($i = $queue_search_idx; $i < sizeof($area_queues[$row['from_area_id']]); $i++) {
              $cur_driver_id = $area_queues[$row['from_area_id']][$i];

              // если клиент отказался от водителя, его мы запоминаем в queue_data заявки
              if (isset($od_queue)) {
                $order_data = json_decode($od_queue->queue_data, true);
                if (is_array($order_data) && in_array($cur_driver_id, $order_data))
                  continue;
              }

              // если водитель свободен, заявку требуется выдать ему
              if (!isset($do_queues[$cur_driver_id])) {
                //echo "Водитель №". $cur_driver_id ." свободен\n";

                // проверим, нет ли заявки в кэше водителя
                if (isset($driver_queues[$cur_driver_id])) {
                  //echo "Поиск кэша водителя..\n";
                  $queue_data = json_decode($driver_queues[$cur_driver_id]->queue_data, true);
                  if (sizeof($queue_data) >= $area_orders[$row['from_area_id']]) {
                    $queue_data = array();

                    $driver_queues[$cur_driver_id]->queue_data = json_encode(array());
                    $driver_queues[$cur_driver_id]->save(true, array('queue_data'));

                    //echo "Кэш водителя превысил количество заявок, обнуляем\n";
                  }

                  // если заявка найдена, ее требуется передать дальше
                  if (in_array($row['order_id'], $queue_data)) {
                    //echo "Заявка найдена у водителя, пропускаем его\n";
                    continue;
                  }
                }

                $do_queues[$cur_driver_id] = $row['order_id'];
                //echo "Выдаем заявку водителю\n";

                // водители, которые только вошли в зону, должны иметь паузу в 2 секунды перед выдачей заявки для
                // большего шанса получения ее на мобильный телефон
                /** @var TaxiDriverPosition $position */
                $position = TaxiDriverPosition::model()->findByPk($cur_driver_id);
                if (time() - strtotime($position->lastupdate) <= 2) {
                  break;
                }

                if (!isset($od_queue)) {
                  $od_queue = new TaxiDriverOrderQueue();
                  $od_queue->order_id = $row['order_id'];
                  $od_queue->queue_idx = $i;
                  $od_queue->save();
                } else {
                  $od_queue->queue_idx = $i;
                  $od_queue->queue_time = date("Y-m-d H:i:s");
                  $od_queue->save(true, array('queue_idx', 'queue_time'));
                }

                $row['driver_id'] = $cur_driver_id;

                if (!isset($driver_queues[$cur_driver_id])) {
                  $dq = new TaxiDriverOrderQueue();
                  $dq->driver_id = $cur_driver_id;
                  $dq->queue_data = json_encode(array());
                  $dq->save();

                  $driver_queues[$cur_driver_id] = $dq;
                } else {
                  $driver_queues[$cur_driver_id]->queue_time = date("Y-m-d H:i:s");
                  $driver_queues[$cur_driver_id]->save(true, array('queue_time'));
                }

                $update = $taxiDb->createCommand("UPDATE `orders` SET status = '". TaxiOrder::STATUS_QUEUE ."', driver_id = ". $row['driver_id'] ."
                  WHERE order_id = ". $row['order_id']);
                $update->query();

                //$delete = $taxiDb->createCommand("DELETE FROM `driver_order_queue` WHERE order_id = ". $row['order_id'] ." AND driver_id != ". $cur_driver_id);
                //$delete->query();

                $order = new TaxiOrder();
                $order->order_id = $row['order_id'];
                $order->driver_id = $cur_driver_id;

                $order->from_place = $row['from_place'];
                $order->from_street = $row['from_street'];
                $order->from_house = $row['from_house'];
                $order->from_porch = $row['from_porch'];
                $order->to_place = $row['to_place'];
                $order->to_street = $row['to_street'];
                $order->to_house = $row['to_house'];

                // Канал заявки
                $lp_channel_text = array(
                  $row['order_id'],
                  $order->getDriver(),
                  $order->getDispatcherDriver(),
                  'Не установлена',
                  TaxiOrder::STATUS_QUEUE,
                  Yii::t('taxi', TaxiOrder::STATUS_QUEUE),
                  $row['poll_key'],
                  $row['driver_id'],
                  $row['price'],
                );

                $d = new DateTime('now', new DateTimeZone('Europe/Moscow'));
                $d->setTimezone(new DateTimeZone($city->timezone));

                // Канал водителя
                $lp_driver_channel = array(
                  $row['order_id'],
                  $order->getFrom(),
                  $order->getTo(),
                  //ActiveHtml::ts_gmt(date("Y-m-d H:i:s")),
                  $d->format('Y-m-d H:i:s'),
                  $d->getTimestamp(),
                );

                LPHelper::send('taxi_driver'. $row['driver_id'], implode('<!>', $lp_driver_channel));
                LPHelper::send('taxi_order'. $row['order_id'] .'_'. $row['poll_key'], implode('<!>', $lp_channel_text));

                // Найдем iOS Device Token водителя
                /*if ($order->driver->user->iOSDeviceToken) {
                  Yii::import('application.vendors.*');
                  require_once 'ApnsPHP/Log/Interface.php';
                  require_once 'ApnsPHP/Log/Embedded.php';
                  require_once 'ApnsPHP/Abstract.php';
                  require_once 'ApnsPHP/Push.php';
                  require_once 'ApnsPHP/Message.php';

                  $push = new ApnsPHP_Push(
                    ApnsPHP_Abstract::ENVIRONMENT_SANDBOX,
                    'apple_push_notification_development.pem'
                  );
                  $push->setRootCertificationAuthority('entrust_root_certification_authority.pem');
                  $push->connect();

                  // Instantiate a new Message with a single recipient
                  $message = new ApnsPHP_Message($order->driver->user->iOSDeviceToken);

                  $message->setCustomIdentifier("Message");
                  $message->setText('У вас новый заказ');
                  $message->setSound();
                  $message->setExpiry(30);
                  $push->add($message);
                  $push->send();

                  $push->disconnect();

                  $aErrorQueue = $push->getErrors();
                  if (!empty($aErrorQueue)) {
                    var_dump($aErrorQueue);
                  }

                  //APNSHelper::sendByYupi($row['iOSDeviceToken'], '111');
                }*/

                $order_updated = true;
                break;
              }
            }

            if (!$order_updated && $row['status'] == TaxiOrder::STATUS_QUEUE) {
              $update = $taxiDb->createCommand("UPDATE `orders` SET status = '". TaxiOrder::STATUS_SEARCH ."', driver_id = 0
                    WHERE order_id = ". $row['order_id']);
              $update->query();

              $order = new TaxiOrder();
              $order->order_id = $row['order_id'];
              $order->driver_id = 0;

              // Канал заявки
              $lp_channel_text = array(
                $row['order_id'],
                $order->getDriver(),
                $order->getDispatcherDriver(),
                'Не установлена',
                TaxiOrder::STATUS_SEARCH,
                Yii::t('taxi', TaxiOrder::STATUS_SEARCH),
                $row['poll_key'],
                $order->driver_id,
                $row['price'],
              );

              LPHelper::send('taxi_order'. $row['order_id'] .'_'. $row['poll_key'], implode('<!>', $lp_channel_text));

              if ($od_queue) $od_queue->delete();
            }
          }

          //echo "Транзакция завершена\n";
          //echo "----------\n";

          $transaction->commit();
        } catch (Exception $e) {
          $transaction->rollback();

          //TODO: Убрать перед запуском в эксплуатацию
          $logs = file_get_contents('/var/www/protected/runtime/tsc.log');
          $logs .= $e->getMessage() ."\n\n";

          file_put_contents('/var/www/protected/runtime/tsc.log', $logs);
          exit($e->getMessage());
        }

        sleep(2);
      }
    } else {
      echo "Error. Taxi orders is running\n";
    }
  }
}