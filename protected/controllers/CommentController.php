<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 28.02.13
 * Time: 12:56
 * To change this template use File | Settings | File Templates.
 */

class CommentController extends Controller {
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

  public function actionAdd($hoop_id, $hoop_type) {
    switch  ($hoop_type) {
      case 'good':
        /** @var $hoop Good */
        $hoop = Good::model()->with('purchase')->findByPk($hoop_id);
        break;
      case 'purchase':
        $hoop = Purchase::model()->findByPk($hoop_id);
        break;
    }

    $comment = new Comment();
    $comment->author_id = Yii::app()->user->getId();
    $comment->hoop_id = $hoop_id;
    $comment->hoop_type = $hoop_type;
    $comment->attributes = $_POST['Comment'];
    if (isset($_POST['reply_to_title']) && intval($_POST['reply_to_title']) > 0) $comment->reply_to = $_POST['reply_to_title'];

    $attaches = array();
    if (isset($_POST['Comment']['attach'])) {
      $k = min(sizeof($_POST['Comment']['attach']), 3);
      for ($i = 0; $i < $k; $i++) {
        $attaches[] = $_POST['Comment']['attach'][$i];
      }
    }

    $comment->attaches = json_encode($attaches);
    $result = array();

    if ($comment->save()) {
      $result['success'] = true;
    }
    else {
      foreach ($comment->getErrors() as $attr => $error) {
        $result[ActiveHtml::activeId($comment, $attr)] = $error;
      }
    }

    echo json_encode($result);
    exit;
  }

  public function actionEdit($id) {
    /** @var $comment Comment */
    $comment = Comment::model()->resetScope()->findByPk($id);

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('comment' => $comment)))
    {
      switch  ($comment->hoop_type) {
        case 'good':
          /** @var $hoop Purchase */
          $h = Good::model()->with('purchase')->findByPk($comment->hoop_id);
          $hoop = $h->purchase;
          break;
        case 'purchase':
          $hoop = Purchase::model()->findByPk($comment->hoop_id);
          break;
      }

      if (isset($_POST['Comment'])) {
        $comment->attributes = $_POST['Comment'];

        $attaches = array();
        if (isset($_POST['Comment']['attach'])) {
          $k = min(sizeof($_POST['Comment']['attach']), 3);
          for ($i = 0; $i < $k; $i++) {
            $attaches[] = $_POST['Comment']['attach'][$i];
          }
        }

        $comment->attaches = json_encode($attaches);
        $result = array();

        if ($comment->save()) {
          $result['success'] = true;
          $result['html'] = $this->renderPartial('_comment', array('comment' => $comment, 'hoop' => $hoop), true);
        }
        else {
          foreach ($comment->getErrors() as $attr => $error) {
            $result[ActiveHtml::activeId($comment, $attr)] = $error;
          }
        }

        echo json_encode($result);
        exit;
      }

      echo json_encode(array('html' => $this->renderPartial('edit', array('comment' => $comment), true)));
      exit;
    }
    else
      throw new CHttpException(403, 'В доступе отказано');
  }

  public function actionDelete($id) {
    /** @var $comment Comment */
    $comment = Comment::model()->resetScope()->findByPk($id);

    switch ($comment->hoop_type) {
      case 'good':
        $h = Good::model()->with('purchase')->findByPk($comment->hoop_id);
        $hoop = $h->purchase;
        break;
      case 'purchase':
        $hoop = Purchase::model()->findByPk($comment->hoop_id);
        break;
    }

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Own', array('comment' => $comment)) ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Owner', array('hoop' => $hoop)))
    {
      //$comment->comment_delete = date("Y-m-d H:i:s");
      if (!$comment->markAsDeleted())
        throw new CHttpException(500, 'Удаление комментария невозможно');

      if (!isset($_SESSION['comment.delete'])) $_SESSION['comment.delete'] = array();
      if (!isset($_SESSION['comment.delete'][$comment->author_id])) $_SESSION['comment.delete'][$comment->author_id] = array('count' => 0, 'items' => array(), 'hash' => '');

      $_SESSION['comment.delete'][$comment->author_id]['count']++;
      $restore = $_SESSION['comment.delete'][$comment->author_id]['items'][$comment->comment_id] = substr(md5(time() . $comment->author_id), 8, 8);
      if ($_SESSION['comment.delete'][$comment->author_id]['count'] >= 3) $hash = $_SESSION['comment.delete'][$comment->author_id]['hash'] = substr(md5(time() . $comment->author_id), 0, 8);

      $html = array();
      $html[] = 'Комментарий удален. <a onclick="Comment.restore'. ((isset($_POST['feed'])) ? 'Feed' : '') .'(this, '. $comment->comment_id .', \''. $restore .'\')">Восстановить</a>';
      if (isset($hash)) $html[] = '<br><a onclick="Comment.massDelete('. $comment->hoop_id .', \''. $comment->hoop_type .'\', '. $comment->author_id .', \''. $hash .'\')">Удалить все комментарии пользователя за последний день</a>';

      echo json_encode(array('html' => implode('', $html)));
      exit;
    }
    else
      throw new CHttpException(403, 'В доступе отказано');
  }

  public function actionMassDelete() {
    $hoop_id = $_POST['hoop_id'];
    $hoop_type = $_POST['hoop_type'];
    $author_id = $_POST['author_id'];
    $hash = $_POST['hash'];

    switch  ($hoop_type) {
      case 'good':
        /** @var $hoop Good */
        $h = Good::model()->with('purchase')->findByPk($hoop_id);
        $hoop = $h->purchase;
        break;
      case 'purchase':
        $hoop = Purchase::model()->findByPk($hoop_id);
        break;
    }

    if (Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Super') ||
      Yii::app()->user->checkAccess(RBACFilter::getHierarchy() .'Owner', array('hoop' => $hoop)))
    {
      Comment::massDeleteByAuthor($hoop_type, $hoop_id, $author_id);

      echo json_encode(array('html' => 'Все комментарии пользователя удалены. Результаты будут видны после перезагрузки'));
      exit;
    }
    else
      throw new CHttpException(403, 'В доступе отказано');
  }

  public function actionRestore($id) {
    if (!isset($_POST['hash']))
      throw new CHttpException(500, 'Не переданы все переменные');

    $hash = $_POST['hash'];

    /** @var $comment Comment */
    $comment = Comment::model()->resetScope()->findByPk($id);
    if (!$comment)
      throw new CHttpException(404, 'Комментарий не найден');

    if (!isset($_SESSION['comment.delete']) ||
      !isset($_SESSION['comment.delete'][$comment->author_id]) ||
      !isset($_SESSION['comment.delete'][$comment->author_id]['items'][$comment->comment_id]))
      throw new CHttpException(500, 'Не найдена цепочка последовательностей');

    if ($hash != $_SESSION['comment.delete'][$comment->author_id]['items'][$comment->comment_id])
      throw new CHttpException(500, 'Неверный код восстановления');

    //$comment->comment_delete = null;
    if (!$comment->restore())
      throw new CHttpException(500, 'Ошибка при восстановлении');

    unset($_SESSION['comment.delete'][$comment->author_id]['items'][$comment->comment_id]);
    $_SESSION['comment.delete'][$comment->author_id]['count']--;
  }

  public function actionPeer($hoop_id, $hoop_type) {
    $last_id = intval((isset($_POST['last_id'])) ? $_POST['last_id'] : 0);

    $criteria = new CDbCriteria();
    $criteria->compare('hoop_id', $hoop_id);
    $criteria->compare('hoop_type', $hoop_type);
    $criteria->addCondition('comment_id > :id');
    $criteria->params[':id'] = $last_id;

    switch  ($hoop_type) {
      case 'good':
        /** @var $hoop Good */
        $h = Good::model()->with('purchase')->findByPk($hoop_id);
        $hoop = $h->purchase;
        break;
      case 'purchase':
        $hoop = Purchase::model()->findByPk($hoop_id);
        break;
    }

    $result = array('items' => array(), 'count' => 0, 'last_id' => $last_id);
    $comments = Comment::model()->with('reply')->findAll($criteria);
    foreach ($comments as $comment) {
      if (isset($_POST['feed']) && $_POST['feed'] === true) $result['items'][] = $this->renderPartial('_feedlikereplies', array('comments' => array($comment), 'hoop' => $hoop), true);
      else $result['items'][] = $this->renderPartial('_comment', array('comment' => $comment, 'hoop' => $hoop), true);
    }

    $result['count'] = sizeof($comments);
    if (isset($comment)) $result['last_id'] = $comment->comment_id;

    echo json_encode($result);
    exit;
  }

  public function actionMore($hoop_id, $hoop_type) {
    $first_id = intval($_POST['first_id']);

    switch ($hoop_type) {
      case 'good':
        $h = Good::model()->with('purchase')->findByPk($hoop_id);
        $hoop = $h->purchase;
        break;
      case 'purchase':
        $hoop = Purchase::model()->findByPk($hoop_id);
        break;
    }

    $criteria = new CDbCriteria();
    $criteria->compare('hoop_id', $hoop_id);
    $criteria->compare('hoop_type', $hoop_type);
    $criteria->addCondition('comment_id < :id');
    $criteria->params[':id'] = $first_id;

    $result = array('items' => array());
    $comments = array_reverse(Comment::model()->with('reply')->findAll($criteria));
    foreach ($comments as $comment) {
      if (isset($_POST['feed'])) $result['items'][] = $this->renderPartial('_feedlikereplies', array('comments' => array($comment), 'hoop' => $hoop), true);
      else $result['items'][] = $this->renderPartial('_comment', array('comment' => $comment, 'hoop' => $hoop), true);
    }

    echo json_encode($result);
    exit;
  }
}