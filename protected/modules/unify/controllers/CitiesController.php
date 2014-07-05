<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 12.07.2013
 * Time: 19:38
 * To change this template use File | Settings | File Templates.
 */

class CitiesController extends Controller {
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
    $criteria->limit = Yii::app()->getModule('unify')->citiesPerPage;
    $criteria->offset = $offset;

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('name', $c['name'], true);
    }

    $criteria->order = 'name';

    $cities = City::model()->findAll($criteria);
    $citiesNum = City::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('index', array(
        'cities' => $cities,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $citiesNum,
      ), true);
    }
    else $this->render('index', array(
      'cities' => $cities,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $citiesNum,
    ));
  }

  public function actionAdd() {
    $city = new City();

    // collect user input data
    if(isset($_POST['name']))
    {
      $city->name = $_POST['name'];
      $city->timezone = $_POST['timezone'];
      $city->published = $_POST['published'];

      $result = array();

      if($city->save()) {
        $result['success'] = true;
        $result['message'] = 'Город успешно добавлен';
      }
      else {
        $errors = array();
        foreach ($city->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('city' => $city), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('city' => $city), true);
    }
    else $this->render('add', array('city' => $city));
  }

  public function actionEdit($id) {
    $city = City::model()->findByPk($id);
    if (!$city)
      throw new CHttpException(404, 'Город не найден');

    // collect user input data
    if(isset($_POST['name']))
    {
      $city->name = $_POST['name'];
      $city->timezone = $_POST['timezone'];
      $city->published = $_POST['published'];

      $result = array();

      if($city->save(true, array('name', 'timezone', 'published'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($city->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('city' => $city), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('city' => $city), true);
    }
    else $this->render('edit', array('city' => $city));
  }

  public function actionDelete($id) {
    $city = City::model()->findByPk($id);
    if (!$city)
      throw new CHttpException(404, 'Город не найден');

    $city->delete();

    echo json_encode(array('message' => 'Город успешно удален'));
    exit;
  }
}