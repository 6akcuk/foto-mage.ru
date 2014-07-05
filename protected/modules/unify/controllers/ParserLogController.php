<?php

class ParserLogController extends Controller {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
      array(
        'ext.RBACFilter.RBACFilter',
      ),
    );
  }

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('unify')->parserLogsPerPage;
    $criteria->offset = $offset;
    $criteria->order = 'start_dt DESC';

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('t.message', $c['name']);
    }

    $logs = ParserLog::model()->findAll($criteria);
    $logsNum = ParserLog::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml =  $this->renderPartial('index',
        array(
          'logs' => $logs,
          'c' => $c,
          'offset' => $offset,
          'offsets' => $logsNum,
        ), true);
    }
    else $this->render('index', array(
      'logs' => $logs,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $logsNum,
    ));
  }
}