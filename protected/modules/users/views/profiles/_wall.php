<?php
$page = ($offset + Yii::app()->getModule('users')->wallPostsPerPage) / Yii::app()->getModule('users')->wallPostsPerPage;
$added = false;
?>
<?php foreach ($posts as $post): ?>
  <?php if(!$added): ?> <div rel="page-<?php echo $page ?>"></div> <?php $added = true; endif; ?>
  <?php $this->renderPartial('_wallpost', array('post' => $post, 'offset' => $offset)) ?>
<?php endforeach; ?>