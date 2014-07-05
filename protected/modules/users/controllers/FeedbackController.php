<?php

class FeedbackController extends Controller {
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
    $criteria->limit = Yii::app()->getModule('users')->feedbacksPerPage;
    $criteria->offset = $offset;
    $criteria->order = 'add_date DESC';

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('t.message', $c['name']);
    }

    if (isset($c['city_id']) && $c['city_id']) {
      $criteria->compare('profile.city_id', $c['city_id']);
    }

    if (isset($c['role']) && $c['role']) {
      $criteria->compare('role.itemname', $c['role']);
    }

    // Ограничение по городу
    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City')) {
      $criteria->compare('profile.city_id', Yii::app()->user->model->profile->city_id);
    }

    $feedbacks = Feedback::model()->with(array(
      'author.profile' => array(
        'joinType' => 'LEFT JOIN'
      ),
      'author.profile.city' => array(
        'joinType' => 'LEFT JOIN'
      ),
    ))->findAll($criteria);

    $feedbacksNum = Feedback::model()->with(array(
      'author.profile' => array(
        'joinType' => 'LEFT JOIN'
      ),
      'author.profile.city' => array(
        'joinType' => 'LEFT JOIN'
      ),
    ))->count($criteria);

    $roles = RbacItem::model()->findAll('type = :type', array(':type' => RbacItem::TYPE_ROLE));

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_feedbacklist', array('feedbacks' => $feedbacks, 'offset' => $offset), true);
      }
      else
        $this->pageHtml =  $this->renderPartial('index',
          array(
            'feedbacks' => $feedbacks,
            'roles' => $roles,
            'c' => $c,
            'offset' => $offset,
            'offsets' => $feedbacksNum,
          ), true);
    }
    else $this->render('index', array(
      'feedbacks' => $feedbacks,
      'roles' => $roles,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $feedbacksNum,
    ));
  }
}