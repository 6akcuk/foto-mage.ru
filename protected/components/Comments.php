<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 28.02.13
 * Time: 9:57
 * To change this template use File | Settings | File Templates.
 */
Yii::import('zii.widgets.CPortlet');

class Comments extends CPortlet {
  public $hoop;
  public $hoop_id;
  public $hoop_type;
  public $reply;

  protected function renderContent() {
    if (!$this->hoop) {
      switch ($this->hoop_type) {
        case 'good':
          $h = Good::model()->with('purchase')->findByPk($this->hoop_id);
          $hoop = $h->purchase;
          break;
        case 'purchase':
          $hoop = Purchase::model()->findByPk($this->hoop_id);
          break;
      }
    }

    $criteria = new CDbCriteria();
    $criteria->order = 'creation_date DESC';
    $criteria->compare('hoop_id', $this->hoop_id);
    $criteria->compare('hoop_type', $this->hoop_type);

    $commentsNum = Comment::model()->with('reply')->count($criteria);

    if (!$this->reply) {
      if ($commentsNum > 10) $criteria->limit = 3;
      $comments = array_reverse(Comment::model()->with('author', 'author.profile', 'reply')->findAll($criteria));
    }
    else {
      if ($commentsNum > 10) {
        $criteria->addCondition('comment_id >= :id');
        $criteria->params[':id'] = $this->reply;
      }

      $comments = array_reverse(Comment::model()->with('author', 'author.profile', 'reply')->findAll($criteria));
    }

    $this->render('comments', array('comments' => $comments, 'offsets' => $commentsNum, 'reply' => $this->reply));
  }
}