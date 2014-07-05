<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 20.06.2013
 * Time: 10:48
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

  public function actionGetParams($id) {
    echo json_encode(array('params' => AdvertParam::buildTable($id)));
    Yii::app()->end();
  }

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('advert')->advertPostsPerPage;
    $criteria->offset = $offset;

    if (isset($c['title']) && $c['title']) {
      $criteria->addSearchCondition('title', $c['title'], true);
    }

    // Ограничение по городу
    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City')) {
      $criteria->compare('city_id', Yii::app()->user->model->profile->city_id);
    }

    $criteria->order = 'add_date DESC';

    $posts = AdvertPost::model()->with('category', 'params')->findAll($criteria);
    $postsNum = AdvertPost::model()->count($criteria);

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
    $post = new AdvertPost();

    // collect user input data
    if(isset($_POST['title']))
    {
      $category = AdvertCategory::model()->findByPk($_POST['category_id']);
      if ($category->no_title == 0) $post->setScenario(($category->no_price == 0) ? 'title.price' : 'title');
      else $post->setScenario(($category->no_price == 0) ? 'price' : '');

      $post->category_id = $_POST['category_id'];
      $post->city_id =
        (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
          ? Yii::app()->user->model->profile->city_id
          : $_POST['city_id'];
      $post->author_id = Yii::app()->user->getId();
      $post->title = $_POST['title'];
      $post->fullstory = $_POST['fullstory'];
      $post->price = $_POST['price'];
      $post->fixed = $_POST['fixed'];

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
        $params = $_POST['params'];
        $errors = array();
        $success = true;
        $table = AdvertParam::buildTable($post->category_id);

        // Сверяем, чтобы все параметры были заданы
        foreach ($table as $prm) {
          if (!isset($prm['dependence'])) {
            if ($params[$prm['id']] == "" || $params[$prm['id']] == "0") {
              $success = false;
              $errors[] = 'Параметр '. $prm['label'] .' не задан';
            }
          } else {
            // Выясним, задан ли параметр, от которого зависит другой
            foreach ($params as $param_id => $param_value) {
              if ($param_value == $prm['dependence']) {
                if ($params[$prm['id']] == "" || $params[$prm['id']] == "0") {
                  $success = false;
                  $errors[] = 'Параметр '. $prm['label'] .' не задан';
                }
                break;
              }
            }
          }
        }

        if (!$success) {
          $post->delete();
          $result['message'] = implode('<br/>', $errors);
        }
        else {
          foreach ($params as $pid => $pval) {
            $ppm = new AdvertPostParam();
            $ppm->post_id = $post->post_id;
            $ppm->param_id = $pid;
            $ppm->param_value = $pval;
            $ppm->save();
          }

          $result['success'] = true;
          $result['message'] = 'Объявление успешно добавлено';
        }
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

    $categories = AdvertCategory::model()->findAll('parent_id IS NULL');

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('post' => $post, 'categories' => $categories), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('post' => $post, 'categories' => $categories), true);
    }
    else $this->render('add', array('post' => $post, 'categories' => $categories));
  }

  public function actionEdit($id) {
    $post = AdvertPost::model()->with('params')->findByPk($id);
    if (!$post)
      throw new CHttpException(404, 'Объявление не найдено');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('post' => $post)))
        throw new CHttpException(403, 'У Вас нет прав на редактирование данного объявления');
    }

    $category = AdvertCategory::model()->findByPk($post->category_id);
    if ($category->no_title == 0) $post->setScenario(($category->no_price == 0) ? 'title.price' : 'title');
    else $post->setScenario(($category->no_price == 0) ? 'price' : '');

    // collect user input data
    if(isset($_POST['title']))
    {
      $category = AdvertCategory::model()->findByPk($_POST['category_id']);
      if ($category->no_title == 0) $post->setScenario(($category->no_price == 0) ? 'title.price' : 'title');
      else $post->setScenario(($category->no_price == 0) ? 'price' : '');

      $post->category_id = $_POST['category_id'];
      $post->city_id =
        (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
          ? Yii::app()->user->model->profile->city_id
          : $_POST['city_id'];
      $post->title = $_POST['title'];
      $post->fullstory = $_POST['fullstory'];
      $post->price = $_POST['price'];
      $post->fixed = $_POST['fixed'];

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

      if($post->save(true, array('category_id', 'city_id', 'fullstory', 'title', 'price', 'fixed', 'photo', 'document'))) {
        $params = $_POST['params'];
        $errors = array();
        $success = true;
        $table = AdvertParam::buildTable($post->category_id);

        // Сверяем, чтобы все параметры были заданы
        foreach ($table as $prm) {
          if (!isset($prm['dependence'])) {
            if ($params[$prm['id']] == "" || $params[$prm['id']] == "0") {
              $success = false;
              $errors[] = 'Параметр '. $prm['label'] .' не задан';
            }
          } else {
            // Выясним, задан ли параметр, от которого зависит другой
            foreach ($params as $param_id => $param_value) {
              if ($param_value == $prm['dependence']) {
                if ($params[$prm['id']] == "" || $params[$prm['id']] == "0") {
                  $success = false;
                  $errors[] = 'Параметр '. $prm['label'] .' не задан';
                }
                break;
              }
            }
          }
        }

        if (!$success) {
          $result['message'] = implode('<br/>', $errors);
        }
        else {
          AdvertPostParam::model()->deleteAll('post_id = :id', array(':id' => $post->post_id));

          foreach ($params as $pid => $pval) {
            $ppm = new AdvertPostParam();
            $ppm->post_id = $post->post_id;
            $ppm->param_id = $pid;
            $ppm->param_value = $pval;
            $ppm->save();
          }

          $result['success'] = true;
          $result['message'] = 'Изменения успешно сохранены';
        }
      }
      else {
        $errors = array();
        foreach ($param->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $categories = AdvertCategory::model()->findAll('parent_id IS NULL');

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('post' => $post, 'categories' => $categories, 'params' => AdvertParam::buildTable($post->category_id)), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('post' => $post, 'categories' => $categories, 'params' => AdvertParam::buildTable($post->category_id)), true);
    }
    else $this->render('edit', array('post' => $post, 'categories' => $categories, 'params' => AdvertParam::buildTable($post->category_id)));
  }

  public function actionDelete($id) {
    $post = AdvertPost::model()->findByPk($id);
    if (!$post)
      throw new CHttpException(404, 'Объявление не найдено');

    if (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City')) {
      if (!Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'City', array('post' => $post)))
        throw new CHttpException(403, 'У Вас нет прав на удаление данного объявления');
    }

    $post->performDelete();

    echo json_encode(array('message' => 'Объявление успешно удалено'));
    exit;
  }
}