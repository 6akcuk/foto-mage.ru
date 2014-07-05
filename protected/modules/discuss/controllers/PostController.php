<?php

class PostController extends Controller
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

  public function actionIndex($forum_id, $theme_id, $offset = 0, $post = 0, $scroll = 0) {
    /** @var DiscussForum $forum */
    $forum = DiscussForum::model()->findByPk($forum_id);
    $theme = DiscussTheme::model()->with('author')->findByPk($theme_id);
    if ($forum->access_city > 0 && $forum->access_city != DiscussForum::getUserCity())
      throw new CHttpException(403, 'В доступе отказано. В Вашем городе данный форум отключен');

    if ($forum->access_rights > DiscussForum::getNumericRight())
      throw new CHttpException(403, 'В доступе отказано. Вы не состоите в нужной группе');

    $criteria = new CDbCriteria();
    $criteria->compare('t.forum_id', $forum_id);
    $criteria->compare('t.theme_id', $theme_id);

    $postsNum = DiscussPost::model()->count($criteria);
    if ($post > 0) {
      $pc = clone $criteria;
      $pc->addCondition('t.post_id < :id');
      $pc->params[':id'] = $post;

      $beforeNum = DiscussPost::model()->count($pc);
      $beforePages = floor($beforeNum / Yii::app()->getModule('discuss')->postsPerPage);

      $offset = ($beforePages * Yii::app()->getModule('discuss')->postsPerPage) - Yii::app()->getModule('discuss')->postsPerPage;
    }

    $criteria->limit = Yii::app()->getModule('discuss')->postsPerPage;

    if ($offset === 'last') {
      $pages = ceil($postsNum / $criteria->limit);
      $offset = $pages * $criteria->limit - $criteria->limit;
    }

    $criteria->offset = $offset;

    $posts = DiscussPost::model()->with('author.profile')->findAll($criteria);

    if (Yii::app()->request->isAjaxRequest) {
      if (isset($_POST['pages'])) {
        $this->pageHtml = $this->renderPartial('_posts', array(
          '_post' => $post,
          'theme' => $theme,
          'posts' => $posts,
          'offset' => $offset
        ), true);
      }
      else $this->pageHtml = $this->renderPartial('index', array(
        'forum' => $forum,
        'theme' => $theme,
        '_post' => $post,
        'posts' => $posts,
        'postsNum' => $postsNum,
        'offset' => $offset,
        'scroll' => $scroll,
      ), true);
    }
    else $this->render('index', array(
      'forum' => $forum,
      'theme' => $theme,
      '_post' => $post,
      'posts' => $posts,
      'postsNum' => $postsNum,
      'offset' => $offset,
      'scroll' => $scroll,
    ));
  }

  public function actionCreate($forum_id, $theme_id) {
    /** @var DiscussForum $forum */
    $forum = DiscussForum::model()->findByPk($forum_id);
    if (!$forum)
      throw new CHttpException(404, 'Форум не найден');

    if ($forum->access_city > 0 && $forum->access_city != DiscussForum::getUserCity())
      throw new CHttpException(403, 'В доступе отказано. В Вашем городе данный форум отключен');

    if ($forum->access_rights > DiscussForum::getNumericRight())
      throw new CHttpException(403, 'В доступе отказано. Вы не состоите в нужной группе');

    $theme = DiscussTheme::model()->findByPk($theme_id);
    if (!$theme)
      throw new CHttpException(404, 'Тема не найдена');

    if ($theme->forum_id != $forum_id)
      throw new CHttpException(500, 'Ошибка синхронизации');

    if ($theme->closed == 1)
      throw new CHttpException(500, 'Тема закрыта и не принимает больше сообщения');

    if (isset($_POST['post'])) {
      $post = new DiscussPost();
      $post->forum_id = $forum_id;
      $post->theme_id = $theme_id;
      $post->author_id = Yii::app()->user->getId();
      $post->post = $_POST['post'];
      $post->attaches = json_encode(isset($_POST['attaches']) ? $_POST['attaches'] : array());
      $post->save();

      if (isset($_POST['feed'])) {
        $criteria = new CDbCriteria();
        $criteria->compare('forum_id', $forum_id);
        $criteria->compare('theme_id', $theme_id);

        $postsNum = DiscussPost::model()->count($criteria);

        $criteria->addCondition('post_id > :id');
        $criteria->params[':id'] = $_POST['last_id'];

        $posts = DiscussPost::model()->findAll($criteria);

        echo json_encode(array(
          'html' => $this->renderPartial('application.modules.discuss.views.post._feedlikereplies', array('theme' => $theme, 'posts' => $posts), true),
          'num' => 'Показать все '. Yii::t('app', '{n} комментарий|{n} комментария|{n} комментариев', $postsNum),
          'last_id' => $posts[sizeof($posts) - 1]->post_id,
        ));
        exit;
      }

      echo json_encode(array(
        'success' => true,
      ));
      exit;
    }
  }

  public function actionEdit($post_id) {
    /** @var DiscussPost $post */
    $post = DiscussPost::model()->with('theme')->findByPk($post_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('post' => $post))) {
      if (isset($_POST['post'])) {
        $post->post = $_POST['post'];
        $post->attaches = json_encode(isset($_POST['attaches']) ? $_POST['attaches'] : array());

        if ($post->save()) {
          echo json_encode(array('post_id' => $post_id, 'html' => $this->renderPartial('_posts', array('_post' => 0, 'theme' => $post->theme, 'posts' => array($post), 'offset' => 0), true)));
          exit;
        }
      }

      echo json_encode(array('html' => $this->renderPartial('edit', array('post' => $post), true)));
      exit;
    }
    else
      throw new CHttpException(403, 'Вы не можете редактировать данный пост');
  }

  public function actionDelete($post_id) {
    /** @var DiscussPost $post */
    $post = DiscussPost::model()->with('theme')->findByPk($post_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('post' => $post)) ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Owner', array('theme' => $post->theme)))
    {
      //$comment->comment_delete = date("Y-m-d H:i:s");
      if (!$post->markAsDeleted())
        throw new CHttpException(500, 'Удаление комментария невозможно');

      if (!isset($_SESSION['dc_post.delete'])) $_SESSION['dc_post.delete'] = array();
      if (!isset($_SESSION['dc_post.delete'][$post->author_id])) $_SESSION['dc_post.delete'][$post->author_id] = array('count' => 0, 'items' => array(), 'hash' => '');

      $_SESSION['dc_post.delete'][$post->author_id]['count']++;
      $restore = $_SESSION['dc_post.delete'][$post->author_id]['items'][$post->post_id] = substr(md5(time() . $post->author_id), 8, 8);
      if ($_SESSION['dc_post.delete'][$post->author_id]['count'] >= 3) $hash = $_SESSION['dc_post.delete'][$post->author_id]['hash'] = substr(md5(time() . $post->author_id), 0, 8);

      $html = array();
      $html[] = 'Комментарий удален. <a onclick="Discuss.restore'. (isset($_POST['feed']) ? 'Feed' : '') .'Post('. $post->post_id .', \''. $restore .'\')">Восстановить</a>.';
      if (isset($hash)) $html[] = '<br><a onclick="Discuss.massDelete('. $post->post_id .', '. $post->author_id .', \''. $hash .'\')">Удалить все комментарии пользователя за последний день</a>';

      echo json_encode(array('html' => (isset($_POST['feed'])) ? '<div class="dld">'. implode('', $html) .'</div>' : '<div class="discuss_deleted"><table><tr><td class="discuss_deleted_td">'. implode('', $html) .'</td></tr></table>'));
      exit;
    }
    else
      throw new CHttpException(403, 'В доступе отказано');
  }

  public function actionMassDelete() {
    $post_id = $_POST['post_id'];
    $author_id = $_POST['author_id'];
    $hash = $_POST['hash'];

    /** @var DiscussPost $post */
    $post = DiscussPost::model()->with('theme')->findByPk($post_id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Owner', array('theme' => $post->theme)))
    {
      $post->massDeletePostsByAuthor();

      echo json_encode(array('html' => 'Все комментарии пользователя удалены. Результаты будут видны после перезагрузки'));
      exit;
    }
    else
      throw new CHttpException(403, 'В доступе отказано');
  }

  public function actionRestore($post_id) {
    if (!isset($_POST['hash']))
      throw new CHttpException(500, 'Не переданы все переменные');

    $hash = $_POST['hash'];

    /** @var $comment Comment */
    $post = DiscussPost::model()->resetScope()->findByPk($post_id);
    if (!$post)
      throw new CHttpException(404, 'Комментарий не найден');

    if (!isset($_SESSION['dc_post.delete']) ||
      !isset($_SESSION['dc_post.delete'][$post->author_id]) ||
      !isset($_SESSION['dc_post.delete'][$post->author_id]['items'][$post->post_id]))
      throw new CHttpException(500, 'Не найдена цепочка последовательностей');

    if ($hash != $_SESSION['dc_post.delete'][$post->author_id]['items'][$post->post_id])
      throw new CHttpException(500, 'Неверный код восстановления');

    //$post->comment_delete = null;
    if (!$post->restore())
      throw new CHttpException(500, 'Ошибка при восстановлении');

    unset($_SESSION['dc_post.delete'][$post->author_id]['items'][$post->post_id]);
    $_SESSION['dc_post.delete'][$post->author_id]['count']--;
  }

  public function actionMore($forum_id, $theme_id) {
    $theme = DiscussTheme::model()->findByPk($theme_id);

    $criteria = new CDbCriteria();
    $criteria->compare('forum_id', $forum_id);
    $criteria->compare('theme_id', $theme_id);
    $criteria->addCondition('post_id < :id');
    $criteria->params[':id'] = intval($_POST['first_id']);

    $criteria->limit = 97;

    $posts = DiscussPost::model()->findAll($criteria);

    echo json_encode(array('html' => $this->renderPartial('_feedlikereplies', array('theme' => $theme, 'posts' => $posts), true)));
    exit;
  }
}