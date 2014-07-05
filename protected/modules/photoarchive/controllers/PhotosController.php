<?php

class PhotosController extends Controller
{
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

  public function actionIndex($id, $offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();

    $criteria->compare('t.album_id', $id);
    $criteria->compare('t.owner_id', Yii::app()->user->getId());

    $album = PhotoarchiveAlbum::model()->findByPk($id);
    $photos = PhotoarchivePhoto::model()->findAll($criteria);
    $photosNum = PhotoarchivePhoto::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml =  $this->renderPartial('index',
        array(
          'album' => $album,
          'photos' => $photos,
          'c' => $c,
          'offset' => $offset,
          'offsets' => $photosNum,
        ), true);
    }
    else $this->render('index', array(
      'album' => $album,
      'photos' => $photos,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $photosNum,
    ));
  }

  public function actionDelete($id) {
    /** @var PhotoarchivePhoto $photo */
    $photo = PhotoarchivePhoto::model()->findByPk($id);
    if (!$photo)
      throw new CHttpException(404, 'Фотография не найдена');

    if ($photo->owner_id != Yii::app()->user->getId())
      throw new CHttpException(403, 'В доступе отказано');

    if ($photo->album->cover_id == $id) {
      $photo->album->cover_id = null;
      $photo->album->save(true, array('cover_id'));
    }

    $photo->album->saveCounters(array('photos_num' => -1));
    $photo->delete();

    echo json_encode(array('message' => 'Фотография успешно удалена'));
    exit;
  }
}