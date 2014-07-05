<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 18.06.2013
 * Time: 3:48
 * To change this template use File | Settings | File Templates.
 */

class CategoryController extends Controller {
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
    $criteria->limit = Yii::app()->getModule('market')->marketCategoriesPerPage;
    $criteria->offset = $offset;

    if (isset($c['name']) && $c['name']) {
      $criteria->addSearchCondition('name', $c['name'], true);
    }

    $categories = GoodCategory::model()->findAll($criteria);
    $categoriesNum = GoodCategory::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_categorylist', array('categories' => $categories, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'categories' => $categories,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $categoriesNum,
      ), true);
    }
    else $this->render('index', array(
      'categories' => $categories,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $categoriesNum,
    ));
  }

  public function actionAdd() {
    $category = new GoodCategory('add');

    // collect user input data
    if(isset($_POST['name']))
    {
      $category->parent_id = $_POST['parent_id'];
      $category->name = $_POST['name'];
      $category->no_title = $_POST['no_title'];
      $category->title_form = $_POST['title_form'];
      $category->no_price = $_POST['no_price'];
      $result = array();

      if($category->save()) {
        $result['success'] = true;
        $result['message'] = 'Категория товаров была успешно добавлена';
      }
      else {
        $errors = array();
        foreach ($category->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $categories = GoodCategory::model()->findAll('parent_id IS NULL');

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('category' => $category, 'categories' => $categories), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('category' => $category, 'categories' => $categories), true);
    }
    else $this->render('add', array('category' => $category, 'categories' => $categories));
  }

  public function actionEdit($id) {
    $category = GoodCategory::model()->findByPk($id);
    if (!$category)
      throw new CHttpException(404, 'Категория не найдена');

    $category->setScenario('edit');

    // collect user input data
    if(isset($_POST['name']))
    {
      $category->parent_id = $_POST['parent_id'];
      $category->name = $_POST['name'];
      $category->no_title = $_POST['no_title'];
      $category->title_form = $_POST['title_form'];
      $category->no_price = $_POST['no_price'];
      $result = array();

      if($category->save(true, array('name', 'parent_id', 'no_title', 'title_form', 'no_price'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($category->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    $categories = GoodCategory::model()->findAll('parent_id IS NULL');

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('category' => $category, 'categories' => $categories), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('category' => $category, 'categories' => $categories), true);
    }
    else $this->render('edit', array('category' => $category, 'categories' => $categories));
  }

  public function actionDelete($id) {
    $category = GoodCategory::model()->findByPk($id);
    if (!$category)
      throw new CHttpException(404, 'Категория не найдена');

    $category->performDelete();

    echo json_encode(array('message' => 'Категория товаров успешно удалена'));
    exit;
  }
}