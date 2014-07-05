<?php

class ForumController extends Controller
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

  public function init()
  {
    parent::init();

    if (isset($_GET['act']))
      $this->defaultAction = $_GET['act'];
  }

	public function actionIndex()
	{
    $criteria = new CDbCriteria();
    $criteria->addCondition('t.parent_id IS NULL');
    $criteria->addCondition('t.access_city = 0 OR t.access_city = :city');
    $criteria->params[':city'] = DiscussForum::getUserCity();
    $criteria->addCondition('t.access_rights <= :rights');
    $criteria->params[':rights'] = DiscussForum::getNumericRight();

    $forums = DiscussForum::model()->findAll($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('index', array(
        'forums' => $forums,
      ), true);
    }
    else $this->render('index', array(
      'forums' => $forums,
    ));
	}

  public function actionCreate()
  {
    if (isset($_POST['title'])) {
      $forum = new DiscussForum();
      $forum->title = $_POST['title'];
      $forum->description = $_POST['description'];
      $forum->parent_id = ($_POST['parent_id'] > 0) ? $_POST['parent_id'] : null;
      $forum->icon = $_POST['icon'];
      $forum->access_city = $_POST['city'];
      $forum->access_rights = $_POST['rights'];

      if ($forum->save()) {
        echo json_encode(array(
          'success' => true,
          'msg' => 'Форум успешно создан',
          'url' => '/discuss?act=manage'
        ));
        exit;
      }
      else
        throw new CHttpException(500, 'Не удалось создать форум');
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('create', array(
      ), true);
    }
    else $this->render('create', array(
    ));
  }

  public function actionEdit($id) {
    $forum = DiscussForum::model()->findByPk($id);

    if (isset($_POST['title'])) {
      $forum->title = $_POST['title'];
      $forum->description = $_POST['description'];
      $forum->parent_id = ($_POST['parent_id'] > 0) ? $_POST['parent_id'] : null;
      $forum->icon = $_POST['icon'];
      $forum->access_city = $_POST['city'];
      $forum->access_rights = $_POST['rights'];

      if ($forum->save(true, array('title', 'description', 'parent_id', 'icon'))) {
        echo json_encode(array(
          'success' => true,
          'msg' => 'Изменения успешно сохранены',
          'url' => '/discuss?act=manage'
        ));
        exit;
      }
      else
        throw new CHttpException(500, 'Не удалось отредактировать форум');
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('edit', array(
        'forum' => $forum,
        'id' => $id,
      ), true);
    }
    else $this->render('edit', array(
      'forum' => $forum,
      'id' => $id,
    ));
  }

  public function actionDelete($id) {
    $forum = DiscussForum::model()->findByPk($id);
    if (!$forum->parent_id) {
      $subforums = DiscussForum::model()->findAll(array(
        'select' => 'forum_id',
        'condition' => 'parent_id = :id',
        'params' => array(':id' => $id),
      ));

      foreach ($subforums as $subforum) {
        $subforum->destroyForum();
      }
    }

    $forum->destroyForum();

    echo json_encode(array(
      'success' => true,
      'msg' => 'Форум успешно удален',
      'url' => '/discuss?act=manage'
    ));
    exit;
  }

  public function actionManage()
  {
    $forums = DiscussForum::model()->findAll('t.parent_id IS NULL');

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('manage', array(
        'forums' => $forums,
      ), true);
    }
    else $this->render('manage', array(
      'forums' => $forums,
    ));
  }
}