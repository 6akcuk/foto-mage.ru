<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 18.06.2013
 * Time: 6:10
 * To change this template use File | Settings | File Templates.
 */

class ParamController extends Controller {
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
    $result = array();
    $params = AdvertParam::model()->findAll('category_id = :id AND parent_id IS NULL', array(':id' => $id));
    /** @var AdvertParam $param */
    foreach ($params as $param) {
      $result[] = array(intval($param->param_id), $param->title, true, false);
      foreach ($param->childs as $child) {
        $result[] = array(intval($child->param_id), $child->title, false, false, 2);
        foreach ($child->childs as $wow) {
          $result[] = array(intval($wow->param_id), $child->title .' '. $wow->title, true, false, 3);
        }
      }
    }

    echo json_encode(array('items' => $result));
    Yii::app()->end();
  }

  public function actionIndex($offset = 0) {
    $c = (isset($_REQUEST['c'])) ? $_REQUEST['c'] : array();

    $criteria = new CDbCriteria();
    $criteria->limit = Yii::app()->getModule('advert')->advertParamsPerPage;
    $criteria->offset = $offset;

    if (isset($c['title']) && $c['title']) {
      $criteria->addSearchCondition('title', $c['title'], true);
    }

    $params = AdvertParam::model()->findAll($criteria);
    $paramsNum = AdvertParam::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_paramlist', array('params' => $params, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'params' => $params,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $paramsNum,
      ), true);
    }
    else $this->render('index', array(
      'params' => $params,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $paramsNum,
    ));
  }

  public function actionAdd() {
    $param = new AdvertParam('add');

    // collect user input data
    if(isset($_POST['title']))
    {
      $result = array();
      $title = $_POST['title'];

      if (preg_match("/(\d+)\-\>(\d+)/ui", $title, $num_op)) {
        if ($num_op[1] < $num_op[2]) {
          for ($i = intval($num_op[1]); $i <= intval($num_op[2]); $i++) {
            $param = new AdvertParam('add');
            $param->parent_id = ($_POST['parent_id'] == 0) ? null : $_POST['parent_id'];
            $param->title = $i;
            $param->category_id = $_POST['category_id'];
            $param->type = $_POST['type'];
            $param->suffix = $_POST['suffix'];
            $param->save();
          }

          $result['success'] = true;
          $result['message'] = 'Параметры были успешно добавлены';
        } else {
          for ($i = $num_op[1]; $i >= $num_op[2]; $i--) {
            $param = new AdvertParam('add');
            $param->parent_id = ($_POST['parent_id'] == 0) ? null : $_POST['parent_id'];
            $param->title = $i;
            $param->category_id = $_POST['category_id'];
            $param->type = $_POST['type'];
            $param->suffix = $_POST['suffix'];
            $param->save();
          }

          $result['success'] = true;
          $result['message'] = 'Параметры были успешно добавлены';
        }
      }
      else {
        $param->parent_id = ($_POST['parent_id'] == 0) ? null : $_POST['parent_id'];
        $param->title = $_POST['title'];
        $param->category_id = $_POST['category_id'];
        $param->type = $_POST['type'];
        $param->suffix = $_POST['suffix'];

        if($param->save()) {
          $result['success'] = true;
          $result['message'] = 'Параметр был успешно добавлен';
        }
        else {
          $errors = array();
          foreach ($param->getErrors() as $attr => $error) {
            $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
          }
          $result['message'] = implode('<br/>', $errors);
        }
      }

      echo json_encode($result);
      exit;
    }

    $categories = AdvertCategory::model()->findAll('parent_id IS NULL');

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addBox', array('param' => $param, 'categories' => $categories), true);
      }
      else $this->pageHtml = $this->renderPartial('add', array('param' => $param, 'categories' => $categories), true);
    }
    else $this->render('add', array('param' => $param, 'categories' => $categories));
  }

  public function actionEdit($id) {
    $param = AdvertParam::model()->findByPk($id);
    if (!$param)
      throw new CHttpException(404, 'Параметр не найден');

    $param->setScenario('edit');

    // collect user input data
    if(isset($_POST['title']))
    {
      $param->parent_id = $_POST['parent_id'];
      $param->title = $_POST['title'];
      $param->category_id = $_POST['category_id'];
      $param->type = $_POST['type'];
      $param->suffix = ($param->suffix && $_POST['type'] != 'input') ? null : $_POST['suffix'];
      $result = array();

      if($param->save(true, array('title', 'parent_id', 'category_id', 'type', 'suffix'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
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
    $params = AdvertParam::model()->findAll('category_id = :id AND parent_id IS NULL', array(':id' => $param->category_id));

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editBox', array('param' => $param, 'params' => $params, 'categories' => $categories), true);
      }
      else $this->pageHtml = $this->renderPartial('edit', array('param' => $param, 'params' => $params, 'categories' => $categories), true);
    }
    else $this->render('edit', array('param' => $param, 'params' => $params, 'categories' => $categories));
  }

  public function actionDelete($id) {
    $param = AdvertParam::model()->findByPk($id);
    if (!$param)
      throw new CHttpException(404, 'Параметр не найден');

    $param->performDelete();

    echo json_encode(array('message' => 'Параметр успешно удален'));
    exit;
  }
}