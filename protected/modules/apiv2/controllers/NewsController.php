<?php
/**
 * Created by PhpStorm.
 * User: Sum
 * Date: 04.01.14
 * Time: 14:58
 */

class NewsController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  public function actionList($city_id, $offset = 0, $density = null) {
    Yii::import('news.models.*');

    $criteria = new CDbCriteria();
    $criteria->compare('t.city_id', $city_id);
    $criteria->offset = $offset;
    $criteria->limit = 10;
    $criteria->order = 'add_date DESC';

    $posts = News::model()->with('author')->findAll($criteria);
    $offsets = News::model()->with('author')->count($criteria);

    $items = array();
    /** @var News $post */
    foreach ($posts as $post) {
      $items[] = array(
        'id' => $post->news_id,
        'title' => $post->title,
        //'fullstory' => $post->fullstory,
        'facephoto' => ActiveHtml::getPhotoUrl($post->facephoto, 'd', $density),
        'date' => ActiveHtml::timeback($post->add_date, true),
        'author' => $post->author->getDisplayName(),
        'views' => $post->views_num,
      );
    }

    $this->json = array(
      'limit' => $criteria->limit,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
    );
  }

  public function actionView($id) {
    Yii::import('news.models.*');

    /** @var News $post */
    $post = News::model()->with('author')->findByPk($id);

    /** @var CDbConnection $db */
    $db = Yii::app()->db;
    $db->createCommand("UPDATE `news` SET views_num = views_num + 1 WHERE news_id = ". intval($id))->query();

    if (!$post)
      throw new CHttpException(404, 'Новость не найдена');

    $this->json = array(
      'id' => $post->news_id,
      'title' => $post->title,
      'fullstory' => $post->fullstory,
      'facephoto' => ActiveHtml::getPhotoUrl($post->facephoto, 'w'),
      'date' => ActiveHtml::timeback($post->add_date, true),
      'author' => $post->author->getDisplayName(),
      'views' => $post->views_num,
    );
  }
} 