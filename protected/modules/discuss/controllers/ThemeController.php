<?php

class ThemeController extends Controller
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

  public function actionIndex($forum_id, $offset = 0)
  {
    /** @var DiscussForum $forum */
    $forum = DiscussForum::model()->findByPk($forum_id);
    if ($forum->access_city > 0 && $forum->access_city != DiscussForum::getUserCity())
      throw new CHttpException(403, 'В доступе отказано. В Вашем городе данный форум отключен');

    if ($forum->access_rights > DiscussForum::getNumericRight())
      throw new CHttpException(403, 'В доступе отказано. Вы не состоите в нужной группе');

    $subcriteria = new CDbCriteria();
    $subcriteria->compare('t.forum_id', $forum_id);
    $subcriteria->select = 'MAX(t.post_id) AS post_id';
    $subcriteria->group = 't.theme_id';
    $subcriteria->offset = $offset;
    $subcriteria->limit = Yii::app()->getModule('discuss')->themesPerPage;

    $posts = DiscussPost::model()->findAll($subcriteria);
    $post_ids = array();

    foreach ($posts as $post) {
      $post_ids[] = $post->post_id;
    }

    $criteria = new CDbCriteria();
    $criteria->addInCondition('t.post_id', $post_ids);
    $criteria->order = 'theme.fixed DESC, t.add_date DESC';
    $criteria->offset = $offset;
    $criteria->limit = Yii::app()->getModule('discuss')->themesPerPage;

    $posts = DiscussPost::model()->with('theme')->findAll($criteria);
    $themes = array();

    foreach ($posts as $post) {
      $post->theme->lastPost = $post;
      $themes[] = $post->theme;
    }
    //$themes = DiscussTheme::model()->with('postsNum', 'lastPost')->findAll($criteria);

    $criteria->limit = 0;
    $themesNum = DiscussPost::model()->count($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_themes', array(
          'themes' => $themes,
          'offset' => $offset
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'forum' => $forum,
        'themes' => $themes,
        'themesNum' => $themesNum,
        'offset' => $offset,
      ), true);
    }
    else $this->render('index', array(
      'forum' => $forum,
      'themes' => $themes,
      'themesNum' => $themesNum,
      'offset' => $offset,
    ));
  }

  public function actionCreate($forum_id)
  {
    /** @var DiscussForum $forum */
    $forum = DiscussForum::model()->findByPk($forum_id);
    if ($forum->access_city > 0 && $forum->access_city != DiscussForum::getUserCity())
      throw new CHttpException(403, 'В доступе отказано. В Вашем городе данный форум отключен');

    if ($forum->access_rights > DiscussForum::getNumericRight())
      throw new CHttpException(403, 'В доступе отказано. Вы не состоите в нужной группе');

    if (isset($_POST['title'])) {
      $theme = new DiscussTheme();
      $theme->forum_id = $forum_id;
      $theme->title = $_POST['title'];
      $theme->author_id = Yii::app()->user->getId();
      if ($theme->save()) {
        $post = new DiscussPost();
        $post->forum_id = $forum_id;
        $post->theme_id = $theme->theme_id;
        $post->author_id = Yii::app()->user->getId();
        $post->post = $_POST['post'];
        $post->attaches = json_encode(isset($_POST['attaches']) ? $_POST['attaches'] : array());
        $post->save();

        echo json_encode(array(
          'success' => true,
          'url' => '/discuss'. $forum_id .'_'. $theme->theme_id,
        ));
        exit;
      }
      else
        throw new CHttpException(500, 'Не удалось создать тему');
    }

    if (Yii::app()->request->isAjaxRequest) {
      $this->pageHtml = $this->renderPartial('create', array(
        'forum' => $forum,
      ), true);
    }
    else $this->render('create', array(
      'forum' => $forum,
    ));
  }

  public function actionSave($theme_id) {
    /** @var DiscussTheme $theme */
    $theme = DiscussTheme::model()->findByPk($theme_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('theme' => $theme))) {
      $theme->title = $_POST['title'];
      $theme->save(true, array('title'));

      echo json_encode(array('title' => $theme->title));
      exit;
    }
    else
      throw new CHttpException(403, 'У Вас нет прав на редактирование темы');
  }

  public function actionFix($theme_id) {
    /** @var DiscussTheme $theme */
    $theme = DiscussTheme::model()->findByPk($theme_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('theme' => $theme))) {
      $theme->fixed = 1;
      $theme->save(true, array('fixed'));

      $_SESSION['discuss.message.title'] = 'Тема закреплена.';
      $_SESSION['discuss.message.body'] = 'Теперь эта тема всегда будет выводиться над остальными в списке обсуждений.';

      echo json_encode(array('url' => '/discuss'. $theme->forum_id .'_'. $theme->theme_id));
      exit;
    }
    else
      throw new CHttpException(403, 'У Вас нет прав на редактирование темы');
  }
  public function actionUnfix($theme_id) {
    /** @var DiscussTheme $theme */
    $theme = DiscussTheme::model()->findByPk($theme_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('theme' => $theme))) {
      $theme->fixed = 0;
      $theme->save(true, array('fixed'));

      $_SESSION['discuss.message.title'] = 'Тема больше не закреплена.';
      $_SESSION['discuss.message.body'] = 'Эта тема будет выводиться на своем месте в списке обсуждений.';

      echo json_encode(array('url' => '/discuss'. $theme->forum_id .'_'. $theme->theme_id));
      exit;
    }
    else
      throw new CHttpException(403, 'У Вас нет прав на редактирование темы');
  }

  public function actionClose($theme_id) {
    /** @var DiscussTheme $theme */
    $theme = DiscussTheme::model()->findByPk($theme_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('theme' => $theme))) {
      $theme->closed = 1;
      $theme->save(true, array('closed'));

      $_SESSION['discuss.message.title'] = 'Тема закрыта.';
      $_SESSION['discuss.message.body'] = 'Участники сообщества больше не смогут оставлять сообщения в этой теме.';

      echo json_encode(array('url' => '/discuss'. $theme->forum_id .'_'. $theme->theme_id));
      exit;
    }
    else
      throw new CHttpException(403, 'У Вас нет прав на редактирование темы');
  }
  public function actionOpen($theme_id) {
    /** @var DiscussTheme $theme */
    $theme = DiscussTheme::model()->findByPk($theme_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('theme' => $theme))) {
      $theme->closed = 0;
      $theme->save(true, array('closed'));

      $_SESSION['discuss.message.title'] = 'Тема открыта.';
      $_SESSION['discuss.message.body'] = 'Все участники сообщества смогут оставлять сообщения в этой теме.';

      echo json_encode(array('url' => '/discuss'. $theme->forum_id .'_'. $theme->theme_id));
      exit;
    }
    else
      throw new CHttpException(403, 'У Вас нет прав на редактирование темы');
  }

  public function actionDelete($theme_id) {
    /** @var DiscussTheme $theme */
    $theme = DiscussTheme::model()->findByPk($theme_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('theme' => $theme))) {
      $theme->destroyTheme();

      echo json_encode(array('url' => '/discuss'. $theme->forum_id, 'msg' => 'Тема успешно удалена.'));
      exit;
    }
    else
      throw new CHttpException(403, 'У Вас нет прав на удаление темы');
  }
}