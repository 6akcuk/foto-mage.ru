<?php

class DeliveryController extends Controller {
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

  public function actionSettings($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->with('org_type', 'city', 'modules')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр данной организации');
    }

    $settings = DeliverySettings::model()->findByPk($id);
    if (!$settings) {
      $settings = new DeliverySettings();
      $settings->org_id = $id;
      $settings->save();
    }

    if (isset($_POST['fullstory'])) {
      $sms_phone = preg_replace("#[^0-9]#", "", $_POST['sms_phone']);

      $settings->fullstory = $_POST['fullstory'];
      $settings->logo = $_POST['logo'];
      $settings->sms_phone = $sms_phone;
      $settings->disable_cart = $_POST['disable_cart'];

      if ($settings->save(true, array('fullstory', 'logo', 'sms_phone', 'disable_cart'))) {
        $result['success'] = true;
        $result['message'] = 'Настройки успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($settings->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $this->pageHtml = $this->renderPartial('settingsBox', array(
      'settings' => $settings,
    ), true);
  }

  public function actionIndex($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->with('org_type', 'city', 'modules')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр данной организации');
    }

    $cats = DeliveryCoLink::model()->with('category')->findAll('org_id = :id', array(':id' => $id));
    $categories = array();
    /** @var DeliverCoLink $category */
    foreach ($cats as $category) {
      $criteria = new CDbCriteria();
      $criteria->compare('org_id', $id);
      $criteria->compare('category_id', $category->category_id);
      $criteria->limit = 4;

      $elles = DeliveryMenuElement::model()->findAll($criteria);
      $categories[$category->category_id] = array('items' => $elles, 'model' => $category);
    }

    $criteria = new CDbCriteria();
    $criteria->compare('org_id', $id);
    $criteria->limit = 4;

    $goods = DeliveryGood::model()->findAll($criteria);
    $goodsNum = DeliveryGood::model()->count($criteria);

    $criteria = new CDbCriteria();
    $criteria->compare('org_id', $id);
    $criteria->limit = 10;
    $criteria->order = 'add_date DESC';

    $orders = DeliveryOrder::model()->with('items')->findAll($criteria);
    $ordersNum = DeliveryOrder::model()->count($criteria);

    $settings = DeliverySettings::model()->findByPk($id);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('index', array(
        'org' => $org,
        'categories' => $categories,
        'goods' => $goods,
        'goodsNum' => $goodsNum,
        'orders' => $orders,
        'ordersNum' => $ordersNum,
        'settings' => $settings,
      ), true);
    }
    else $this->render('index', array(
      'org' => $org,
      'categories' => $categories,
      'goods' => $goods,
      'goodsNum' => $goodsNum,
      'orders' => $orders,
      'ordersNum' => $ordersNum,
      'settings' => $settings,
    ));
  }

  public function actionOrder($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $order = DeliveryOrder::model()->with('items')->findByPk($id);
    if (!$order)
      throw new CHttpException(500, 'Заказ не найден');

    $org = Organization::model()->findByPk($order->org_id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр заказа данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр заказа данной организации');
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('order', array(
        'org' => $org,
        'order' => $order,
      ), true);
    }
    else $this->render('order', array(
      'org' => $org,
      'order' => $order,
    ));
  }

  public function actionOrders($id, $offset = 0) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр заказов данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр заказов данной организации');
    }

    $criteria = new CDbCriteria();
    $criteria->compare('t.org_id', $id);
    $criteria->offset = $offset;
    $criteria->limit = Yii::app()->getModule('orgs')->deliveryOrdersPerPage;
    $criteria->order = 't.add_date DESC';

    $orders = DeliveryOrder::model()->with('items')->findAll($criteria);
    $ordersNum = DeliveryOrder::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('orders', array(
        'org' => $org,
        'orders' => $orders,
        'offset' => $offset,
        'offsets' => $ordersNum,
      ), true);
    }
    else $this->render('orders', array(
      'org' => $org,
      'orders' => $orders,
      'offset' => $offset,
      'offsets' => $ordersNum,
    ));
  }

  public function actionGoods($id, $offset = 0) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр товаров данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр товаров данной организации');
    }

    $criteria = new CDbCriteria();
    $criteria->compare('t.org_id', $id);
    $criteria->offset = $offset;
    $criteria->limit = Yii::app()->getModule('orgs')->deliveryGoodsPerPage;

    $goods = DeliveryGood::model()->with('element')->findAll($criteria);
    $goodsNum = DeliveryGood::model()->with('element')->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('goods', array(
        'org' => $org,
        'goods' => $goods,
        'offset' => $offset,
        'offsets' => $goodsNum,
      ), true);
    }
    else $this->render('goods', array(
      'org' => $org,
      'goods' => $goods,
      'offset' => $offset,
      'offsets' => $goodsNum,
    ));
  }

  public function actionAddGood($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на добавление товара в меню данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на добавление товара в меню данной организации');
    }

    $good = new DeliveryGood();
    if (isset($_POST['name'])) {
      $good->org_id = $id;
      $good->currency = 'RUR';
      $good->element_id = $_POST['element_id'];
      $good->discount = $_POST['discount'];
      $good->facephoto = $_POST['facephoto'];
      $good->name = $_POST['name'];
      $good->price = $_POST['price'];
      $good->shortstory = $_POST['shortstory'];

      if ($good->save()) {
        $result['success'] = true;
        $result['message'] = 'Товар успешно добавлен';
      }
      else {
        $errors = array();
        foreach ($good->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $cats = DeliveryCoLink::model()->with('category')->findAll('org_id = :id', array(':id' => $id));
    $categories = array();
    /** @var DeliverCoLink $category */
    foreach ($cats as $category) {
      $criteria = new CDbCriteria();
      $criteria->compare('org_id', $id);
      $criteria->compare('category_id', $category->category_id);

      $elles = DeliveryMenuElement::model()->findAll($criteria);
      $categories[$category->category_id] = array('items' => $elles, 'model' => $category);
    }

    $this->pageHtml = $this->renderPartial('addGoodBox', array(
      'org' => $org,
      'categories' => $categories,
      'good' => $good,
    ), true);
  }

  public function actionEditGood($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по товару');

    $good = DeliveryGood::model()->findByPk($id);
    if (!$good)
      throw new CHttpException(404, 'Товар не найден');

    $org = Organization::model()->findByPk($good->org_id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование товара меню данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на редактирование товара меню данной организации');
    }

    if (isset($_POST['name'])) {
      $good->element_id = $_POST['element_id'];
      $good->discount = $_POST['discount'];
      $good->facephoto = $_POST['facephoto'];
      $good->name = $_POST['name'];
      $good->price = $_POST['price'];
      $good->shortstory = $_POST['shortstory'];

      if ($good->save(true, array('element_id', 'discount', 'facephoto', 'name', 'price', 'shortstory'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($good->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $cats = DeliveryCoLink::model()->with('category')->findAll('org_id = :id', array(':id' => $good->org_id));
    $categories = array();
    /** @var DeliverCoLink $category */
    foreach ($cats as $category) {
      $criteria = new CDbCriteria();
      $criteria->compare('org_id', $good->org_id);
      $criteria->compare('category_id', $category->category_id);

      $elles = DeliveryMenuElement::model()->findAll($criteria);
      $categories[$category->category_id] = array('items' => $elles, 'model' => $category);
    }

    $this->pageHtml = $this->renderPartial('editGoodBox', array(
      'org' => $org,
      'categories' => $categories,
      'good' => $good,
    ), true);
  }

  public function actionDeleteGood($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по товару');

    $good = DeliveryGood::model()->findByPk($id);
    if (!$good)
      throw new CHttpException(404, 'Товар не найден');

    $org = Organization::model()->findByPk($good->org_id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на удаление меню данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на удаление меню данной организации');
    }

    $good->delete();

    exit;
  }

  public function actionMenu($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на просмотр меню данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на просмотр меню данной организации');
    }

    $cats = DeliveryCoLink::model()->with('category')->findAll('org_id = :id', array(':id' => $id));
    $categories = array();
    /** @var DeliverCoLink $category */
    foreach ($cats as $category) {
      $elles = DeliveryMenuElement::model()->findAll('org_id = :id AND category_id = :cid', array(':id' => $id, ':cid' => $category->category_id));
      $categories[$category->category_id] = array('items' => $elles, 'model' => $category);
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('menu', array(
        'org' => $org,
        'categories' => $categories,
      ), true);
    }
    else $this->render('menu', array(
      'org' => $org,
      'categories' => $categories,
    ));
  }

  public function actionAddMenuElement($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на добавление меню данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на добавление меню данной организации');
    }

    $element = new DeliveryMenuElement();
    if (isset($_POST['name'])) {
      $element->org_id = $id;
      $element->category_id = $_POST['category_id'];
      $element->name = $_POST['name'];
      $element->icon = $_POST['icon'];

      if ($element->save()) {
        $check = DeliveryCoLink::model()->find('org_id = :id AND category_id = :cid', array(':id' => $id, ':cid' => $element->category_id));
        if (!$check) {
          $check = new DeliveryCoLink();
          $check->category_id = $element->category_id;
          $check->org_id = $id;
          $check->save();
        }

        $result['success'] = true;
        $result['message'] = 'Элемент успешно добавлен';
      }
      else {
        $errors = array();
        foreach ($element->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $categories = DeliveryCategory::model()->findAll();

    $this->pageHtml = $this->renderPartial('addMenuElementBox', array(
      'org' => $org,
      'categories' => $categories,
      'element' => $element,
    ), true);
  }

  public function actionEditMenuElement($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по элементу');

    $element = DeliveryMenuElement::model()->findByPk($id);
    if (!$element)
      throw new CHttpException(404, 'Элемент не найден');

    $org = Organization::model()->findByPk($element->org_id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование меню данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на редактирование меню данной организации');
    }

    if (isset($_POST['name'])) {
      $element->category_id = $_POST['category_id'];
      $element->name = $_POST['name'];
      $element->icon = $_POST['icon'];

      if ($element->save(true, array('category_id', 'icon', 'name'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($element->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $categories = DeliveryCategory::model()->findAll();

    $this->pageHtml = $this->renderPartial('editMenuElementBox', array(
      'org' => $org,
      'categories' => $categories,
      'element' => $element,
    ), true);
  }

  public function actionDeleteMenuElement($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по элементу');

    $element = DeliveryMenuElement::model()->findByPk($id);
    if (!$element)
      throw new CHttpException(404, 'Элемент не найден');

    $org = Organization::model()->findByPk($element->org_id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на удаление меню данной организации');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на удаление меню данной организации');
    }

    $element->performDelete();

    exit;
  }

  public function actionCategories($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('orgs')->deliveryCategoriesPerPage;
    $criteria->offset = $offset;

    if (isset($c['title']) && $c['title']) {
      $criteria->addSearchCondition('title', $c['title'], true);
    }

    $categories = DeliveryCategory::model()->findAll($criteria);
    $categoriesNum = DeliveryCategory::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_categorylist', array('categories' => $categories, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('categories', array(
        'categories' => $categories,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $categoriesNum,
      ), true);
    }
    else $this->render('categories', array(
      'categories' => $categories,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $categoriesNum,
    ));
  }

  public function actionAddCategory() {
    $category = new DeliveryCategory('add');

    // collect user input data
    if(isset($_POST['name']))
    {
      $category->name = $_POST['name'];
      $result = array();

      if($category->save()) {
        $result['success'] = true;
        $result['message'] = 'Категория доставки была успешно добавлена';
      }
      else {
        $errors = array();
        foreach ($category->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addCategoryBox', array('category' => $category), true);
      }
      else $this->pageHtml = $this->renderPartial('addCategory', array('category' => $category), true);
    }
    else $this->render('addCategory', array('category' => $category));
  }

  public function actionEditCategory($id) {
    $category = DeliveryCategory::model()->findByPk($id);
    if (!$category)
      throw new CHttpException(404, 'Категория не найдена');

    $category->setScenario('edit');

    // collect user input data
    if(isset($_POST['name']))
    {
      $category->name = $_POST['name'];
      $result = array();

      if($category->save(true, array('name'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($category->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editCategoryBox', array('category' => $category), true);
      }
      else $this->pageHtml = $this->renderPartial('editCategory', array('category' => $category), true);
    }
    else $this->render('editCategory', array('category' => $category));
  }

  public function actionDeleteCategory($id) {
    $category = DeliveryCategory::model()->findByPk($id);
    if (!$category)
      throw new CHttpException(404, 'Категория не найдена');

    $category->delete();

    echo json_encode(array('message' => 'Категория доставки успешно удалена'));
    exit;
  }
}