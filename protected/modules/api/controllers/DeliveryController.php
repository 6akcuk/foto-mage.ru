<?php

class DeliveryController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  public function actionCategories() {
    Yii::import('orgs.models.*');

    $categories = DeliveryCategory::model()->findAll();
    /** @var DeliveryCategory $cat */
    foreach ($categories as $cat) {
      $this->json[] = array('id' => $cat->category_id, 'name' => $cat->name);
    }
  }

  public function actionOrgs($category_id, $offset = 0, $city_id = null) {
    Yii::import('orgs.models.*');

    $this->authorize();

    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('t.category_id', $category_id);
    $criteria->compare('org.city_id', $city_id);
    $criteria->offset = $offset;
    $criteria->limit = 10;

    $links = DeliveryCoLink::model()->with('org')->findAll($criteria);
    /** @var DeliveryCoLink $link */
    foreach ($links as $link) {
      $this->json[] = array('id' => $link->org->org_id, 'name' => $link->org->name);
    }
  }

  public function actionMenu($category_id, $org_id, $density = null) {
    Yii::import('orgs.models.*');

    $this->authorize();

    $criteria = new CDbCriteria();
    $criteria->compare('category_id', $category_id);
    $criteria->compare('org_id', $org_id);

    /** @var Organization $org */
    $org = Organization::model()->with('settings')->findByPk($org_id);
    $elements = DeliveryMenuElement::model()->findAll($criteria);
    $items = array();
    /** @var DeliveryMenuElement $element */
    foreach ($elements as $element) {
      $items[] = array(
        'id' => $element->element_id,
        'name' => $element->name,
        'icon' => ActiveHtml::getPhotoUrl($element->icon, 'b', $density),
      );
    }

    $linksNum = DeliveryCoLink::model()->count('org_id = :id', array(':id' => $org_id));

    $this->json = array(
      'logo' => ($org->settings && $org->settings->logo) ? ActiveHtml::getPhotoUrl($org->settings->logo, 'd', $density) : ActiveHtml::getPhotoUrl($org->photo, 'd', $density),
      'fullstory' => ($org->settings) ? $org->settings->fullstory : '',
      'many_menus' => ($linksNum > 1) ? true : false,
      'items' => $items,
    );
  }

  public function actionMenus($org_id, $density = null) {
    Yii::import('orgs.models.*');

    $this->authorize();

    /** @var Organization $org */
    $org = Organization::model()->with('settings')->findByPk($org_id);

    $cats = DeliveryCoLink::model()->with('category')->findAll('org_id = :id', array(':id' => $org_id));
    $categories = array();
    /** @var DeliveryCoLink $category */
    foreach ($cats as $category) {
      $elles = DeliveryMenuElement::model()->findAll('org_id = :id AND category_id = :cid', array(':id' => $org_id, ':cid' => $category->category_id));
      $items = array();
      /** @var DeliveryMenuElement $element */
      foreach ($elles as $element) {
        $items[] = array(
          'id' => $element->element_id,
          'name' => $element->name,
          'icon' => ActiveHtml::getPhotoUrl($element->icon, 'b', $density),
        );
      }

      $categories[] = array('items' => $items, 'name' => $category->category->name);
    }

    $this->json = array(
      'logo' => ($org->settings && $org->settings->logo) ? ActiveHtml::getPhotoUrl($org->settings->logo, 'd', $density) : ActiveHtml::getPhotoUrl($org->photo, 'd', $density),
      'fullstory' => ($org->settings) ? $org->settings->fullstory : '',
      'categories' => $categories,
    );
  }

  public function actionGoods($element_id, $offset = null, $density = null) {
    Yii::import('orgs.models.*');

    $this->authorize();

    $criteria = new CDbCriteria();
    $criteria->compare('element_id', $element_id);

    if ($offset >= 0) {
      $criteria->offset = $offset;
      $criteria->limit = 20;
    }

    /** @var DeliveryMenuElement $element */
    $element = DeliveryMenuElement::model()->with('org', 'org.settings')->findByPk($element_id);
    $goods = DeliveryGood::model()->findAll($criteria);
    $offsets = DeliveryGood::model()->count($criteria);

    $items = array();
    /** @var DeliveryGood $good */
    foreach ($goods as $good) {
      $items[] = array(
        'id' => $good->good_id,
        'name' => $good->name,
        'shortstory' => $good->shortstory,
        'price' => ActiveHtml::price($good->price, $good->currency),
        'photo' => ActiveHtml::getPhotoUrl($good->facephoto, 'b', $density),
      );
    }

    $this->json = array(
      'org_id' => $element->org->org_id,
      'org_name' => $element->org->name,
      'logo' => ($element->org->settings && $element->org->settings->logo) ? ActiveHtml::getPhotoUrl($element->org->settings->logo, 'd', $density) : ActiveHtml::getPhotoUrl($element->org->photo, 'd', $density),
      'fullstory' => ($element->org->settings) ? $element->org->settings->fullstory : '',
      'disable_cart' => ($element->org->settings) ? !!$element->org->settings->disable_cart : false,
      'items' => $items,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
    );
  }

  public function actionGood($good_id) {
    Yii::import('orgs.models.*');

    $good = DeliveryGood::model()->findByPk($good_id);
    if (!$good)
      throw new CHttpException(404, 'Товар не найден');

    $this->json = array(
      'id' => $good->good_id,
      'name' => $good->name,
      'shortstory' => $good->shortstory,
      'price' => $good->price,
      'photo' => ActiveHtml::getPhotoUrl($good->facephoto, 'b', null),
    );
  }

  public function actionCart($density = null) {
    $this->authorize();

    $cart = (is_array($_POST['cart'])) ? $_POST['cart'] : json_decode($_POST['cart'], true);
    $good_ids = array();
    $goods_table = array();
    foreach ($cart as $crt) {
      $good_ids[] = $crt['good_id'];
      $goods_table[$crt['good_id']] = $crt['amount'];
    }

    $criteria = new CDbCriteria();
    $criteria->addInCondition('good_id', $good_ids);

    $goods = DeliveryGood::model()->findAll($criteria);
    $items = array();
    /** @var DeliveryGood $good */
    foreach ($goods as $good) {
      $items[] = array(
        'good_id' => $good->good_id,
        'photo' => ActiveHtml::getPhotoUrl($good->facephoto, 'c', $density),
        'name' => $good->name,
        'price' => $good->price,
        'amount' => $goods_table[$good->good_id],
      );
    }

    $this->json = $items;
  }

  public function actionOrder() {
    $this->authorize();

    if (Yii::app()->user->getIsGuest())
      throw new CHttpException(401, 'Требуется авторизация');

    $cart = (is_array($_POST['cart'])) ? $_POST['cart'] : json_decode($_POST['cart'], true);
    $good_ids = array();
    $goods_table = array();
    foreach ($cart as $crt) {
      $good_ids[] = $crt['good_id'];
      $goods_table[$crt['good_id']] = $crt['amount'];
    }

    $criteria = new CDbCriteria();
    $criteria->addInCondition('good_id', $good_ids);

    $goods = DeliveryGood::model()->findAll($criteria);
    $org = false;
    $summary = 0;
    $items = array();
    /** @var DeliveryGood $good */
    foreach ($goods as $good) {
      $summary += (int) ($good->price * $goods_table[$good->good_id]);

      /** @var Organization $org */
      if (!$org) $org = Organization::model()->with('settings')->findByPk($good->org_id);
    }

    if ($org->settings && $org->settings->disable_cart == 1) {
      throw new CHttpException(500, 'Возможность заказов отключена');
    }

    $sms = array();

    $order = new DeliveryOrder();
    $order->address = $_POST['address'];
    $order->additional = $_POST['additional'];
    $order->persons_num = $_POST['persons_num'];
    $order->currency = 'RUR';
    $order->owner_id = Yii::app()->user->getId();
    $order->org_id = $org->org_id;
    $order->phone = Yii::app()->user->model->email; // Denis 18.07.2013 - OMG
    $order->status = DeliveryOrder::STATUS_PROCEEDING;
    $order->summary = $summary;

    $result = array();

    if ($order->save()) {
      $sms[] = "Заказ №". $order->order_id;
      $sms[] = "Адрес: ". $order->address;
      $sms[] = "Телефон: ". $order->phone;
      $sms[] = "Персон: ". $order->persons_num;
      $sms[] = "Дополнительно: ". $order->additional;
      $sms[] = "Сумма заказа: ". ActiveHtml::price($order->summary, $order->currency);
      $sms[] = "Состав заказа";
      $sms[] = "----";

      foreach ($goods as $good) {
        $item = new DeliveryOrderItem();
        $item->currency = $good->currency;
        $item->amount = $goods_table[$good->good_id];
        $item->good_name = $good->name;
        $item->good_photo = $good->facephoto;
        $item->price = $good->price;
        $item->order_id = $order->order_id;
        $item->save();

        $sms[] = $good->name ." - ". $item->amount ." шт.";
      }

      // Отправка СМС организации
      /** @var Organization $org */
      if ($org->settings && $org->settings->sms_phone) {
        $deli = new SmsDelivery(Yii::app()->params['smsUsername'], Yii::app()->params['smsPassword']);
        $deli->SendMessage(preg_replace("/^8(\d+)/ui", "+7$1", $org->settings->sms_phone), Yii::app()->params['smsNumber'], implode("\r\n", $sms));
      }

      $result['success'] = true;
    }
    else {
      $errors = array();
      foreach ($order->getErrors() as $attr => $error) {
        $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
      }
      $result['errors'] = implode('<br/>', $errors);
    }

    $this->json = $result;
  }
}