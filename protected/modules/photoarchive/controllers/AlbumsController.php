<?php

class AlbumsController extends Controller
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

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->order = 't.album_id DESC';

    $criteria->compare('t.owner_id', Yii::app()->user->getId());

    $albums = PhotoarchiveAlbum::model()->with('cover')->findAll($criteria);
    $albumsNum = PhotoarchiveAlbum::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml =  $this->renderPartial('index',
        array(
          'albums' => $albums,
          'c' => $c,
          'offset' => $offset,
          'offsets' => $albumsNum,
        ), true);
    }
    else $this->render('index', array(
      'albums' => $albums,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $albumsNum,
    ));
  }

  public function actionAdd() {
    $album = new PhotoarchiveAlbum();

    if (isset($_POST['name'])) {
      $album->name = $_POST['name'];
      $album->owner_id = Yii::app()->user->getId();

      if ($album->save()) {
        $result['success'] = true;
        $result['message'] = 'Альбом успешно создан';
      } else {
        $errors = array();
        foreach ($album->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('album' => $album), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('album' => $album), true);
    }
    else $this->render('add', array('album' => $album));
  }

  public function actionEdit($id) {
    /** @var PhotoarchiveAlbum $album */
    $album = PhotoarchiveAlbum::model()->findByPk($id);
    if (!$album)
      throw new CHttpException(404, 'Альбом не найден');

    if ($album->owner_id != Yii::app()->user->getId())
      throw new CHttpException(403, 'В доступе отказано');

    if (isset($_POST['name'])) {
      $album->name = $_POST['name'];

      if ($album->save(true, array('name'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      } else {
        $errors = array();
        foreach ($album->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('album' => $album), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('album' => $album), true);
    }
    else $this->render('edit', array('album' => $album));
  }

  public function actionDelete($id) {
    /** @var PhotoarchiveAlbum $album */
    $album = PhotoarchiveAlbum::model()->findByPk($id);
    if (!$album)
      throw new CHttpException(404, 'Альбом не найден');

    if ($album->owner_id != Yii::app()->user->getId())
      throw new CHttpException(403, 'В доступе отказано');

    $album->delete();

    echo json_encode(array('message' => 'Альбом успешно удален'));
    exit;
  }
}