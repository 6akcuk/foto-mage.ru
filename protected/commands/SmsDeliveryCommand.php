<?php

class SmsDeliveryCommand extends CConsoleCommand {
  public function actionOrder() {
    if (Yii::app()->mutex->lock('sms-delivery-order', 5)) {
      set_time_limit(50);
      /** @var $conn CDbConnection */
      $conn = Yii::app()->db;
      $notin = array(OrderSmsDeliveryLog::STATUS_REJECTED, OrderSmsDeliveryLog::STATUS_RECEIVED);

      $command = $conn->createCommand("
        SELECT l.log_id, l.phone, l.status, l.message_id, d.message FROM `orders_sms_delivery_logs` l
          INNER JOIN `orders_sms_deliveries` d ON d.delivery_id = l.delivery_id
          WHERE l.status NOT IN (". implode(", ", $notin) .")");

      $reader = $command->query();
      while (($row = $reader->read()) !== false) {
        if ($row['status'] == OrderSmsDeliveryLog::STATUS_QUEUE) {
          $sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
          $result = $sms->SendMessage($row['phone'], Yii::app()->params['smsNumber'], $row['message']);

          $conn->createCommand("
            UPDATE `orders_sms_delivery_logs`
              SET status = ". OrderSmsDeliveryLog::STATUS_SENDED .", message_id = ". intval($result['messageId']) ."
              WHERE log_id = ". $row['log_id'])->query();
        }
        elseif ($row['status'] == OrderSmsDeliveryLog::STATUS_SENDED) {
          $sms = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
          $result = $sms->GetMessageStatus(intval($row['message_id']));

          if ($result == 'Доставлено') {
            $conn->createCommand("
              UPDATE `orders_sms_delivery_logs`
                SET status = ". OrderSmsDeliveryLog::STATUS_RECEIVED ."
                WHERE log_id = ". $row['log_id'])->query();
          }
          elseif ($result == 'Отклонено') {
            $conn->createCommand("
              UPDATE `orders_sms_delivery_logs`
                SET status = ". OrderSmsDeliveryLog::STATUS_REJECTED ."
                WHERE log_id = ". $row['log_id'])->query();
          }
        }
      }

      Yii::app()->mutex->unlock();
      return 0;
    }
    else return 1;
  }
}