<?php

class MarketController extends Controller {
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

  public function actionIndex($id, $offset = 0) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->with('modules')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (!$org->modules || ($org->modules && $org->modules->enable_market == 0))
      throw new CHttpException(500, 'Модуль системы товаров и услуг не активирован');

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

    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('market')->marketGoodsPerPage;
    $criteria->offset = $offset;

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('t.name', $c['name'], true);
    }
    if (isset($c['category_id']) && $c['category_id']) {
      $criteria->compare('t.category_id', $c['category_id']);
    }

    $criteria->compare('t.org_id', $id);

    $goods = MarketGood::model()->with('category')->findAll($criteria);
    $goodsNum = MarketGood::model()->count($criteria);

    $categories = GoodCategory::model()->findAll('parent_id IS NULL');

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_goodlist', array('posts' => $goods, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'org' => $org,
        'categories' => $categories,
        'goods' => $goods,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $goodsNum,
      ), true);
    }
    else $this->render('index', array(
      'org' => $org,
      'categories' => $categories,
      'goods' => $goods,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $goodsNum,
    ));
  }

  public function actionAddGood($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по организации');

    $org = Organization::model()->with('modules')->findByPk($id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (!$org->modules || ($org->modules && $org->modules->enable_market == 0))
      throw new CHttpException(500, 'Модуль системы товаров и услуг не активирован');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('org' => $org)))
        throw new CHttpException(403, 'У Вас нет прав на добавление товара в данную организацию');
    }
    // Ограничение на закрепленные организации
    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'Moderate')) {
      $link = UserOrgLink::model()->find('user_id = :id AND org_id = :org_id', array(':id' => Yii::app()->user->getId(), ':org_id' => $id));
      if (!$link)
        throw new CHttpException(403, 'У Вас нет прав на добавление товара в данную организацию');
    }

    $good = new MarketGood();
    if (isset($_POST['name'])) {
      $good->org_id = $id;
      $good->currency = 'RUR';
      $good->category_id = $_POST['category_id'];
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

    $categories = GoodCategory::model()->findAll('parent_id IS NULL');

    $this->pageHtml = $this->renderPartial('addGoodBox', array(
      'org' => $org,
      'categories' => $categories,
      'good' => $good,
    ), true);
  }

  public function actionEditGood($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по товару');

    $good = MarketGood::model()->findByPk($id);
    if (!$good)
      throw new CHttpException(404, 'Товар не найден');

    $org = Organization::model()->with('modules')->findByPk($good->org_id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (!$org->modules || ($org->modules && $org->modules->enable_market == 0))
      throw new CHttpException(500, 'Модуль системы товаров и услуг не активирован');

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
      $good->category_id = $_POST['category_id'];
      $good->discount = $_POST['discount'];
      $good->facephoto = $_POST['facephoto'];
      $good->name = $_POST['name'];
      $good->price = $_POST['price'];
      $good->shortstory = $_POST['shortstory'];

      if ($good->save(true, array('category_id', 'discount', 'facephoto', 'name', 'price', 'shortstory'))) {
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

    $categories = GoodCategory::model()->findAll('parent_id IS NULL');

    $this->pageHtml = $this->renderPartial('editGoodBox', array(
      'org' => $org,
      'categories' => $categories,
      'good' => $good,
    ), true);
  }

  public function actionDeleteGood($id) {
    if ($id == 0)
      throw new CHttpException(500, 'Нет данных по товару');

    $good = MarketGood::model()->findByPk($id);
    if (!$good)
      throw new CHttpException(404, 'Товар не найден');

    $org = Organization::model()->with('modules')->findByPk($good->org_id);
    if (!$org)
      throw new CHttpException(404, 'Организация не найдена');

    if (!$org->modules || ($org->modules && $org->modules->enable_market == 0))
      throw new CHttpException(500, 'Модуль системы товаров и услуг не активирован');

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
}