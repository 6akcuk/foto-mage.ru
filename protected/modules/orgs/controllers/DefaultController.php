<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 16.10.12
 * Time: 20:41
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
    $criteria->limit = Yii::app()->getModule('orgs')->orgTypesPerPage;
    $criteria->offset = $offset;

    if (isset($c['type_name']) && $c['type_name']) {
      $criteria->addSearchCondition('type_name', $c['type_name'], true);
    }

    $types = OrganizationType::model()->with('org_num')->findAll($criteria);
    $typesNum = OrganizationType::model()->with('org_num')->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_typelist', array('types' => $types, 'offset' => $offset), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'types' => $types,
        'c' => $c,
        'offset' => $offset,
        'offsets' => $typesNum,
      ), true);
    }
    else $this->render('index', array(
      'types' => $types,
      'c' => $c,
      'offset' => $offset,
      'offsets' => $typesNum,
    ));
  }

  public function actionAddType() {
    $type = new OrganizationType('add');

    // collect user input data
    if(isset($_POST['type_name']))
    {
      $type->type_name = $_POST['type_name'];
      $type->afisha = $_POST['afisha'];
      $result = array();

      if($type->save()) {
        $result['success'] = true;
        $result['message'] = 'Тип организации был успешно добавлен';
      }
      else {
        $errors = array();
        foreach ($type->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('addTypeBox', array('type' => $type), true);
      }
      else $this->pageHtml = $this->renderPartial('addType', array('type' => $type), true);
    }
    else $this->render('addType', array('type' => $type));
  }

  public function actionEditType($id) {
    $type = OrganizationType::model()->findByPk($id);
    if (!$type)
      throw new CHttpException(404, 'Тип не найден');

    $type->setScenario('edit');

    // collect user input data
    if(isset($_POST['type_name']))
    {
      $type->type_name = $_POST['type_name'];
      $type->afisha = $_POST['afisha'];
      $result = array();

      if($type->save(true, array('type_name', 'afisha'))) {
        $result['success'] = true;
        $result['message'] = 'Изменения успешно сохранены';
      }
      else {
        $errors = array();
        foreach ($type->getErrors() as $attr => $error) {
          $errors[] = (is_array($error)) ? implode('<br/>', $error) : $error;
        }
        $result['message'] = implode('<br/>', $errors);
      }

      echo json_encode($result);
      exit;
    }

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['box'])) {
        $this->pageHtml = $this->renderPartial('editTypeBox', array('type' => $type), true);
      }
      else $this->pageHtml = $this->renderPartial('editType', array('type' => $type), true);
    }
    else $this->render('editType', array('type' => $type));
  }

  public function actionDeleteType($id) {
    $type = OrganizationType::model()->findByPk($id);
    if (!$type)
      throw new CHttpException(404, 'Тип не найден');

    $type->delete();

    echo json_encode(array('message' => 'Тип организации успешно удален'));
    exit;
  }

  public function actionSearchType($query) {
    $criteria = new CDbCriteria();
    $criteria->limit = 20;
    $criteria->order = 'type_name';

    $criteria->addSearchCondition('type_name', $query);

    $result = array();

    $types = OrganizationType::model()->findAll($criteria);
    /** @var OrganizationType $type */
    foreach ($types as $type) {
      $result[] = array($type->type_id, $type->type_name);
    }

    echo json_encode($result);
    exit;
  }
}