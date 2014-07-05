<?php

class NewsController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  public function actionPosts($offset = 0, $density = null, $city_id = null) {
    Yii::import('news.models.*');

    $this->authorize();

    $criteria = new CDbCriteria();

    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;

    $criteria->compare('t.city_id', $city_id);
    $criteria->offset = $offset;
    $criteria->limit = 10;
    $criteria->order = 'add_date DESC';

    $posts = News::model()->with('author')->findAll($criteria);
    $offsets = News::model()->with('author')->count($criteria);

    $weight = rand(0, 100);

    $criteria = new CDbCriteria();
    $criteria->compare('city_id', $city_id);
    $criteria->addCondition('weight >= :weight');
    $criteria->params[':weight'] = $weight;

    $criteria->order = 'RAND()';
    $criteria->limit = 1;
    /** @var AdsNews $ad */
    $ad = AdsNews::model()->cache(1800)->find($criteria);

    $items = array();
    /** @var News $post */
    foreach ($posts as $post) {
      $items[] = array(
        'id' => $post->news_id,
        'title' => $post->title,
        //'fullstory' => $post->fullstory,
        'facephoto' => ActiveHtml::getPhotoUrl($post->facephoto, 'c', $density),
        'date' => ActiveHtml::timeback($post->add_date, true),
        //'author' => $post->author->getDisplayName()
      );
    }

    $this->json = array(
      'limit' => 10,
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
      'ad' => ($ad) ? ActiveHtml::getPhotoUrl($ad->banner, 'd', $density) : null,
    );
  }

  public function actionPost($id) {
    $this->authorize();

    Yii::import('news.models.*');

    /** @var News $post */
    $post = News::model()->with('author')->findByPk($id);

    if (!$post)
      throw new CHttpException(404, 'Новость не найдена');

    $this->json = array(
      'id' => $post->news_id,
      'title' => $post->title,
      'fullstory' => $post->fullstory,
      'facephoto' => ActiveHtml::getPhotoUrl($post->facephoto, 'd'),
      'date' => ActiveHtml::timeback($post->add_date, true),
      'author' => $post->author->getDisplayName()
    );
  }
}