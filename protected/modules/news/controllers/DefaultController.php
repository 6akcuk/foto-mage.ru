<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 12.07.2013
 * Time: 19:38
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

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('news')->newsPostsPerPage;
    $criteria->offset = $offset;

    if (isset($c['title']) && $c['title']) {
      $criteria->addSearchCondition('title', $c['title'], true);
    }

    // Ограничение по городу
    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City')) {
      $criteria->compare('city_id', Yii::app()->user->model->profile->city_id);
    }

    $criteria->order = 'add_date DESC';

    $posts = News::model()->with('author', 'city')->findAll($criteria);
    $postsNum = News::model()->with('author', 'city')->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_postlist', array('posts' => $posts, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'posts' => $posts,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $postsNum,
      ), true);
    }
    else $this->render('index', array(
      'posts' => $posts,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $postsNum,
    ));
  }

  public function actionAdd() {
    $post = new News();

    // collect user input data
    if(isset($_POST['title']))
    {
      $post->city_id =
        (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
          ? Yii::app()->user->model->profile->city_id
          : $_POST['city_id'];
      $post->author_id = Yii::app()->user->getId();
      $post->title = $_POST['title'];
      $post->fullstory = $_POST['fullstory'];
      $post->facephoto = $_POST['facephoto'];

      // Прикрепленные фотографии
      if (isset($_POST['attaches']['photo'])) {
        $photo = array();
        foreach ($_POST['attaches']['photo'] as $ph) {
          $photo[] = json_decode($ph, true);
        }

        $post->photo = json_encode($photo);
      }
      // Прикрепленные документы
      if (isset($_POST['attaches']['document'])) {
        $doc = array();
        foreach ($_POST['attaches']['document'] as $dc) {
          $doc[] = json_decode($dc, true);
        }

        $post->document = json_encode($doc);
      }

      $result = array();

      if($post->save()) {
        $result['success'] = true;
        $result['message'] = 'Новость успешно добавлена';
      }
      else {
        $errors = array();
        foreach ($post->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('post' => $post), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('post' => $post), true);
    }
    else $this->render('add', array('post' => $post));
  }

  public function actionEdit($id) {
    $post = News::model()->findByPk($id);
    if (!$post)
      throw new CHttpException(404, 'Новость не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('post' => $post)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование данной новости');
    }

    // collect user input data
    if(isset($_POST['title']))
    {
      $post->city_id =
        (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
          ? Yii::app()->user->model->profile->city_id
          : $_POST['city_id'];
      $post->title = $_POST['title'];
      $post->fullstory = $_POST['fullstory'];
      $post->facephoto = $_POST['facephoto'];

      // Прикрепленные фотографии
      if (isset($_POST['attaches']['photo'])) {
        $photo = array();
        foreach ($_POST['attaches']['photo'] as $ph) {
          $photo[] = json_decode($ph, true);
        }

        $post->photo = json_encode($photo);
      }
      // Прикрепленные документы
      if (isset($_POST['attaches']['document'])) {
        $doc = array();
        foreach ($_POST['attaches']['document'] as $dc) {
          $doc[] = json_decode($dc, true);
        }

        $post->document = json_encode($doc);
      }

      $result = array();

      if($post->save(true, array('city_id', 'fullstory', 'title', 'price', 'facephoto', 'photo', 'document'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($post->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('post' => $post), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('post' => $post), true);
    }
    else $this->render('edit', array('post' => $post));
  }

  public function actionDelete($id) {
    $post = News::model()->findByPk($id);
    if (!$post)
      throw new CHttpException(404, 'Новость не найдена');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('post' => $post)))
        throw new CHttpException(403, 'У Вас нет прав на удаление данной новости');
    }

    $post->delete();

    echo json_encode(array('message' => 'Новость успешно удалена'));
    exit;
  }
}