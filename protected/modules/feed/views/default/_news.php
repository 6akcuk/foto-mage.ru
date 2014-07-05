<?php
/** @var Feed $feed */
?>
<?php foreach ($feeds as $feed): ?>
<div class="feed_row">
  <?php if ($feed->event_type == 'new post'): ?>
  <?php $this->renderPartial('application.modules.users.views.profiles._wallpost', array('post' => $feed->content, 'timeback' => true)) ?>
  <?php elseif ($feed->event_type == 'new purchase'): ?>
  <?php $this->renderPartial('application.modules.purchases.views.purchases._feedpurchase', array('feed' => $feed)) ?>
  <?php elseif ($feed->event_type == 'new good'): ?>
  <?php $this->renderPartial('application.modules.purchases.views.goods._feedgood', array('feed' => $feed)) ?>
  <?php elseif ($feed->event_type == 'new status'): ?>
  <?php $this->renderPartial('application.modules.users.views.profiles._feedstatus', array('feed' => $feed)) ?>
  <?php endif; ?>
</div>
<?php endforeach; ?>