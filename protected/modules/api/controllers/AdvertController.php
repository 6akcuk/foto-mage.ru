<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.10.12
 * Time: 20:41
 * To change this template use File | Settings | File Templates.
 */

class AdvertController extends ApiController {
  public function filters() {
    return array(
      array(
        'ext.AjaxFilter.JSONFilter'
      ),
    );
  }

  public function actionAdd($category_id) {
    Yii::import('advert.models.*');

    /** @var AdvertCategory $category */
    $category = AdvertCategory::model()->findByPk($category_id);

    // collect user input data
    if(isset($_POST['fullstory']))
    {
      if (!$this->authorize())
        throw new CHttpException(500, 'У Вас нет прав на добавление объявления');

      $post = new AdvertPost();
      if ($category->no_title == 0) $post->setScenario(($category->no_price == 0) ? 'title.price' : 'title');
      else $post->setScenario(($category->no_price == 0) ? 'price' : '');

      $post->category_id = $category_id;
      $post->city_id = Yii::app()->user->model->profile->city_id;
      $post->author_id = Yii::app()->user->getId();
      $post->title = (isset($_POST['title'])) ? $_POST['title'] : '';
      $post->fullstory = $_POST['fullstory'];
      $post->price = (isset($_POST['price'])) ? $_POST['price'] : '';
      //$post->fixed = $_POST['fixed'];

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
        $params = (isset($_POST['params'])) ? $_POST['params'] : array();
        $errors = array();
        $success = true;
        $table = AdvertParam::buildTable($post->category_id);

        // Сверяем, чтобы все параметры были заданы
        foreach ($table as $prm) {
          if (!isset($prm['dependence'])) {
            if (!isset($params[$prm['id']]) || (isset($params[$prm['id']]) && ($params[$prm['id']] == "" || $params[$prm['id']] == "0"))) {
              $success = false;
              $errors[] = 'Параметр '. $prm['label'] .' не задан';
            }
          } else {
            // Выясним, задан ли параметр, от которого зависит другой
            foreach ($params as $param_id => $param_value) {
              if ($param_value == $prm['dependence']) {
                if (!isset($params[$prm['id']]) || (isset($params[$prm['id']]) && ($params[$prm['id']] == "" || $params[$prm['id']] == "0"))) {
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
          $result['errors'] = implode('<br/>', $errors);
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
          $result['post_id'] = $post->post_id;
        }
      }
      else {
        $errors = array();
        foreach ($post->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['errors'] = implode('<br/>', $errors);
      }

      $this->json = $result;
    }
    else {
      $this->json = array(
        'table' => AdvertParam::buildTable($category_id),
        'no_title' => $category->no_title,
        'no_price' => $category->no_price,
        'upload_url' => 'http://cs1.'. Yii::app()->params['domain'] .'/upload.php',
      );
    }
  }

  public function actionEdit($id) {
    Yii::import('advert.models.*');

    if (!$this->authorize())
      throw new CHttpException(403, 'Вы не авторизованы');
    /** @var AdvertPost $post */
    $post = AdvertPost::model()->findByPk($id);

    if (Yii::app()->user->checkAccess('advert.default.editSuper') ||
      Yii::app()->user->checkAccess('advert.default.editOwn', array('post' => $post))) {
      /** @var AdvertCategory $category */
      $category = AdvertCategory::model()->findByPk($post->category_id);

      // collect user input data
      if(isset($_POST['fullstory']))
      {
        if ($category->no_title == 0) $post->setScenario(($category->no_price == 0) ? 'title.price' : 'title');
        else $post->setScenario(($category->no_price == 0) ? 'price' : '');

        $post->title = (isset($_POST['title'])) ? $_POST['title'] : '';
        $post->fullstory = $_POST['fullstory'];
        $post->price = (isset($_POST['price'])) ? $_POST['price'] : '';
        //$post->fixed = $_POST['fixed'];

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

        $params = (isset($_POST['params'])) ? $_POST['params'] : array();
        $errors = array();
        $success = true;
        $table = AdvertParam::buildTable($post->category_id);

        // Сверяем, чтобы все параметры были заданы
        foreach ($table as $prm) {
          if (!isset($prm['dependence'])) {
            if (!isset($params[$prm['id']]) || (isset($params[$prm['id']]) && ($params[$prm['id']] == "" || $params[$prm['id']] == "0"))) {
              $success = false;
              $errors[] = 'Параметр '. $prm['label'] .' не задан';
            }
          } else {
            // Выясним, задан ли параметр, от которого зависит другой
            foreach ($params as $param_id => $param_value) {
              if ($param_value == $prm['dependence']) {
                if (!isset($params[$prm['id']]) || (isset($params[$prm['id']]) && ($params[$prm['id']] == "" || $params[$prm['id']] == "0"))) {
                  $success = false;
                  $errors[] = 'Параметр '. $prm['label'] .' не задан';
                }
                break;
              }
            }
          }
        }

        if (!$success) {
          $result['errors'] = implode('<br/>', $errors);
        }
        else {
          if ($post->save(true, array('title', 'fullstory', 'price', 'photo', 'document'))) {
            AdvertPostParam::model()->deleteAll('post_id = :id', array(':id' => $post->post_id));

            foreach ($params as $pid => $pval) {
              $ppm = new AdvertPostParam();
              $ppm->post_id = $post->post_id;
              $ppm->param_id = $pid;
              $ppm->param_value = $pval;
              $ppm->save();
            }

            $result['success'] = true;
            $result['post_id'] = $post->post_id;
          }
          else {
            $errors = array();
            foreach ($post->getErrors() as $attr => $error) {
              $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
            }
            $result['errors'] = implode('<br/>', $errors);
          }
        }

        $this->json = $result;
      }
      else {
        $postParams = AdvertPostParam::model()->findAll('post_id = :id', array(':id' => $id));

        $photos = array();
        if ($post->photo) {
          $pl = json_decode($post->photo, true);
          foreach ($pl as $photo) {
            $photos[] = json_encode($photo);
          }
        }

        /** @var AdvertPostParam $param */
        $params = array();
        foreach ($postParams as $param) {
          $params[] = array(
            'param_id' => $param->param_id,
            'value' => $param->param_value,
          );
        }

        $this->json = array(
          'table' => AdvertParam::buildTable($post->category_id),
          'upload_url' => 'http://cs1.'. Yii::app()->params['domain'] .'/upload.php',
          'no_title' => $category->no_title,
          'no_price' => $category->no_price,
          'post_id' => $post->post_id,
          'category_id' => $post->category_id,
          'title' => $post->title,
          'fullstory' => $post->fullstory,
          'price' => $post->price,
          'params' => $params,
          'photos' => $photos,
        );
      }
    } else {
      throw new CHttpException(403, 'У Вас нет прав на редактирование объявления');
    }
  }

  public function actionDelete($id) {
    if (!$this->authorize())
      throw new CHttpException(403, 'Вы не авторизованы');

    Yii::import('advert.models.*');

    $post = AdvertPost::model()->findByPk($id);

    if (Yii::app()->user->checkAccess('advert.default.deleteSuper') ||
      Yii::app()->user->checkAccess('advert.default.deleteOwn', array('post' => $post))) {
      $post->performDelete();
    } else {
      throw new CHttpException(403, 'У Вас нет прав на удаление объявления');
    }
  }

  public function actionCategories($id = 0, $city_id = null) {
    Yii::import('advert.models.*');
    $this->authorize();

    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;

    $categories = ($id == 0) ? AdvertCategory::model()->findAll('parent_id IS NULL AND category_id NOT IN (1,2)') : AdvertCategory::model()->findAll('parent_id = :id', array(':id' => $id));
    /** @var AdvertCategory $cat */
    foreach ($categories as $cat) {
      if ($cat->parent_id) {
        $postsNum = AdvertPost::model()->count('category_id = :id AND city_id = :cid', array(':id' => $cat->category_id, ':cid' => $city_id));
      } else {
        $postsNum = AdvertPost::model()->count('category_id IN (SELECT category_id FROM `advert_categories` WHERE parent_id = :id) AND city_id = :cid', array(':id' => $cat->category_id, ':cid' => $city_id));
      }
      $this->json[] = array('id' => $cat->category_id, 'name' => $cat->name . (($postsNum > 0) ? ' ('. $postsNum .')' : ''));
    }
  }

  public function actionMy($offset = 0) {
    Yii::import('advert.models.*');

    if (!$this->authorize())
      throw new CHttpException(403, 'Вы не авторизованы');

    $criteria = new CDbCriteria();
    $criteria->compare('author_id', Yii::app()->user->getId());
    $criteria->offset = $offset;
    $criteria->limit = 10;
    $criteria->order = 'add_date DESC';

    $posts = AdvertPost::model()->findAll($criteria);
    $offsets = AdvertPost::model()->count($criteria);
    $items = array();
    /** @var AdvertPost $post */
    foreach ($posts as $post) {
      $items[] = array(
        'id' => $post->post_id,
        'title' => $post->getTitle(),
        'photo' => ActiveHtml::getFirstPhotoUrl($post->photo),
        'price' => ActiveHtml::price($post->price),
        'date' => ActiveHtml::date($post->add_date),
      );
    }

    $this->json = array(
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
    );
  }

  public function actionLast($offset = 0, $city_id = null) {
    Yii::import('advert.models.*');

    $this->authorize();

    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('city_id', $city_id);
    $criteria->offset = $offset;
    $criteria->limit = 10;
    $criteria->order = 'add_date DESC';

    $posts = AdvertPost::model()->with('category')->findAll($criteria);
    $offsets = AdvertPost::model()->count($criteria);
    $items = array();
    /** @var AdvertPost $post */
    foreach ($posts as $post) {
      $items[] = array(
        'id' => $post->post_id,
        'title' => $post->getTitle(),
        'photo' => ActiveHtml::getFirstPhotoUrl($post->photo),
        'price' => ActiveHtml::price($post->price),
        'date' => ActiveHtml::date($post->add_date),
        'no_price' => ($post->category->no_price == 1) ? true : false,
      );
    }

    $this->json = array(
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'items' => $items,
    );
  }

  public function actionPosts($subcategory_id, $offset = 0, $city_id = null) {
    Yii::import('advert.models.*');

    $this->authorize();

    if (!$city_id) $city_id = Yii::app()->user->model->profile->city_id;

    $criteria = new CDbCriteria();
    $criteria->compare('category_id', $subcategory_id);
    $criteria->compare('city_id', $city_id);
    $criteria->offset = $offset;
    $criteria->limit = 10;
    $criteria->order = 'add_date DESC';

    $category = AdvertCategory::model()->findByPk($subcategory_id);

    $posts = AdvertPost::model()->findAll($criteria);
    $offsets = AdvertPost::model()->count($criteria);
    $items = array();
    /** @var AdvertPost $post */
    foreach ($posts as $post) {
      $items[] = array(
        'id' => $post->post_id,
        'title' => $post->getTitle(),
        'photo' => ActiveHtml::getFirstPhotoUrl($post->photo),
        'price' => ActiveHtml::price($post->price),
        'date' => ActiveHtml::date($post->add_date),
      );
    }

    $this->json = array(
      'more' => ($offsets > $offset + $criteria->limit) ? true : false,
      'no_price' => ($category->no_price == 1) ? true : false,
      'items' => $items,
    );
  }

  public function actionPost($id) {
    if (isset($_POST['user_id']) && $_POST['user_id']) $this->authorize();

    Yii::import('advert.models.*');

    /** @var AdvertPost $post */
    $post = AdvertPost::model()->with('author', 'city', 'category')->findByPk($id);
    if (!$post)
      throw new CHttpException(404, 'Объявление не найдено');

    $photos = array();
    if ($post->photo) {
      $pl = json_decode($post->photo, true);
      foreach ($pl as $photo) {
        $photos[] = ActiveHtml::getPhotoUrl($photo, 'b');
      }
    }

    $criteria = new CDbCriteria();
    $criteria->compare('post_id', $id);

    $items = array();
    /** @var AdvertPostParam $param */
    $params = AdvertPostParam::model()->with('param_name')->findAll($criteria);
    foreach ($params as $param) {
      $items[] = array($param->param_name->title, ($param->param_name->type == 'select') ? $param->value->title : $param->param_value);
    }

    $this->json = array(
      'id' => $post->post_id,
      'parent_id' => $post->category->parent_id,
      'title' => $post->getTitle(),
      'photos' => $photos,
      'phone' => $post->author->email,
      'city' => $post->city->name,
      'price' => ActiveHtml::price($post->price),
      'fullstory' => $post->fullstory,
      'params' => $items,
      'no_price' => ($post->category->no_price == 1) ? true : false,
      'can_edit' => (Yii::app()->user->checkAccess('advert.default.editSuper') || Yii::app()->user->checkAccess('advert.default.editOwn', array('post' => $post))) ? true : false,
    );
  }

  public function actionPostPhotos($id, $index = 0) {
    Yii::import('advert.models.*');

    /** @var AdvertPost $post */
    // TODO: Выбирать только фотографии
    $post = AdvertPost::model()->findByPk($id);
    $photos = array();
    $pl = json_decode($post->photo, true);
    foreach ($pl as $photo) {
      $photos[] = ActiveHtml::getPhotoUrl($photo, 'w');
    }

    $this->json = array(
      'photo' => isset($photos[$index]) ? $photos[$index] : $photos[0],
      'photos' => $photos,
    );
  }
}