<?php

class DefaultController extends Controller
{
  public $defaultAction = 'news';

  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.AjaxFilter'
      ),
      array(
        'ext.RBACFilter.RBACFilter'
      ),
    );
  }

  public function init() {
    parent::init();

    if (isset($_GET['section']))
      $this->defaultAction = $_GET['section'];
  }

  public function actionNews($offset = 0, $as = 0)
	{
    $feeds = Feed::getFeeds(($as) ?: Yii::app()->user->getId(), $offset);
    $feedsNum = Feed::countFeeds(($as) ?: Yii::app()->user->getId());

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_news', array(
          'feeds' => $feeds,
          'offset' => $offset,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('news', array(
        'feeds' => $feeds,
        'feedsNum' => $feedsNum,
      ), true);
    }
    else $this->render('news', array(
      'feeds' => $feeds,
      'feedsNum' => $feedsNum,
    ));
	}

  public function actionNotifications($offset = 0, $as = 0) {
    $feeds = Feed::getAnswerFeeds(($as && Yii::app()->user->getId() == 1) ? $as : Yii::app()->user->getId(), $offset);
    $feedsNum = Feed::countAnswerFeeds(($as && Yii::app()->user->getId() == 1) ? $as : Yii::app()->user->getId());

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_notification', array(
          'feeds' => $feeds,
          'offset' => $offset,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('notifications', array(
        'feeds' => $feeds,
        'feedsNum' => $feedsNum,
      ), true);
    }
    else $this->render('notifications', array(
      'feeds' => $feeds,
      'feedsNum' => $feedsNum,
    ));
  }

  public function actionComments($offset = 0, $as = 0) {
    $feeds = Feed::getCommentFeeds(($as && Yii::app()->user->getId() == 1) ? $as : Yii::app()->user->getId(), $offset);
    $feedsNum = Feed::countCommentFeeds(($as && Yii::app()->user->getId() == 1) ? $as : Yii::app()->user->getId());

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_comments', array(
          'feeds' => $feeds,
          'offset' => $offset,
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('comments', array(
        'feeds' => $feeds,
        'feedsNum' => $feedsNum,
      ), true);
    }
    else $this->render('comments', array(
      'feeds' => $feeds,
      'feedsNum' => $feedsNum,
    ));
  }
}