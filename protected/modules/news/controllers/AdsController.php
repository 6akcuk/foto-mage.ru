<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 12.07.2013
 * Time: 19:38
 * To change this template use File | Settings | File Templates.
 */

class AdsController extends Controller {
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
    $criteria->limit = Yii::app()->getModule('news')->adsPerPage;
    $criteria->offset = $offset;

    // Ограничение по городу
    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City')) {
      $criteria->compare('city_id', Yii::app()->user->model->profile->city_id);
    }

    $criteria->order = 'add_date DESC';

    $ads = AdsNews::model()->with('city', 'author.profile')->findAll($criteria);
    $adsNum = AdsNews::model()->with('city', 'author.profile')->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_adslist', array('ads' => $ads, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'ads' => $ads,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $adsNum,
      ), true);
    }
    else $this->render('index', array(
      'ads' => $ads,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $adsNum,
    ));
  }

  public function actionAdd() {
    $ads = new AdsNews();

    // collect user input data
    if(isset($_POST['weight']))
    {
      $ads->city_id =
        (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
          ? Yii::app()->user->model->profile->city_id
          : $_POST['city_id'];
      $ads->author_id = Yii::app()->user->getId();
      $ads->banner = $_POST['banner'];
      $ads->weight = $_POST['weight'];

      $result = array();

      if($ads->save()) {
        $result['success'] = true;
        $result['message'] = 'Баннер успешно добавлен';
      }
      else {
        $errors = array();
        foreach ($ads->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('ads' => $ads), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('ads' => $ads), true);
    }
    else $this->render('add', array('ads' => $ads));
  }

  public function actionEdit($id) {
    $ads = AdsNews::model()->findByPk($id);
    if (!$ads)
      throw new CHttpException(404, 'Баннер не найден');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('ads' => $ads)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование данного баннера');
    }

    // collect user input data
    if(isset($_POST['weight']))
    {
      $ads->city_id =
        (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
          ? Yii::app()->user->model->profile->city_id
          : $_POST['city_id'];
      $ads->author_id = Yii::app()->user->getId();
      $ads->banner = $_POST['banner'];
      $ads->weight = $_POST['weight'];

      $result = array();

      if($ads->save(true, array('city_id', 'banner', 'weight'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($ads->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('ads' => $ads), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('ads' => $ads), true);
    }
    else $this->render('edit', array('ads' => $ads));
  }

  public function actionDelete($id) {
    $ads = AdsNews::model()->findByPk($id);
    if (!$ads)
      throw new CHttpException(404, 'Баннер не найден');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('ads' => $ads)))
        throw new CHttpException(403, 'У Вас нет прав на удаление данного баннера');
    }

    $ads->delete();

    echo json_encode(array('message' => 'Баннер успешно удален'));
    exit;
  }
}