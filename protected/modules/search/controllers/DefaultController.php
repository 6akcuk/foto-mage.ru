<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 05.01.13
 * Time: 22:43
 * To change this template use File | Settings | File Templates.
 */

class DefaultController extends Controller {
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

  public function init() {
    parent::init();

    $c = (!isset($_POST['c'])) ? array('section' => 'people') : $_POST['c'];
    if (!isset($c['section'])) $c['section'] = 'people';

    $this->defaultAction = $c['section'];
  }

  public function actionPeople($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->controller->module->peoplesPerPage;
    $criteria->offset = $offset;

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('t.login', $c['name']);
      $criteria->addSearchCondition('profile.lastname', $c['name'], true, 'OR');
      $criteria->addSearchCondition('profile.firstname', $c['name'], true, 'OR');
    }

    if (isset($c['city_id']) && $c['city_id']) {
      $criteria->addCondition('profile.city_id = :city_id');
      $criteria->params[':city_id'] = $c['city_id'];
    }

    if (isset($c['role']) && $c['role']) {
      $criteria->addCondition('role.itemname = :role');
      $criteria->params[':role'] = $c['role'];
    }

    $peoples = User::model()->with('role', 'profile', 'profile.city')->findAll($criteria);

    $criteria->limit = 0;
    $peoplesNum = User::model()->with('role', 'profile')->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_people', array(
          'peoples' => $peoples,
          'offset' => $offset,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('people', array(
        'peoples' => $peoples,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $peoplesNum,
      ), true);
    }
    else $this->render('people', array('peoples' => $peoples, 'c' => $c, 'offset' => $offset, 'offsets' => $peoplesNum,));
  }
}