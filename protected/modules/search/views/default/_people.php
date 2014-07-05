<?php /** @var $people User */ ?>
<?php $page = ($offset + Yii::app()->controller->module->peoplesPerPage) / Yii::app()->controller->module->peoplesPerPage ?>
<?php $added = false; ?>
<?php foreach ($peoples as $people): ?>
<div<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?> class="people clear_fix">
  <div class="fl_l photo">
    <?php echo ($people->profile->photo) ? ActiveHtml::showUploadImage($people->profile->photo) : '<img src="/images/camera_a.gif" />' ?>
  </div>
  <div class="fl_l info">
    <div class="labeled name">
        <?php echo ActiveHtml::link($people->getDisplayName(), '/id'. $people->id) ?>
    </div>
    <div class="labeled"><?php echo $people->profile->city->name ?></div>
    <?php if ($people->role->itemname != 'Пользователь'): ?>
    <div class="labeled"><?php echo $people->role->itemname ?></div>
    <?php endif; ?>
    <?php if ($people->isOnline()): ?>
    <div class="online">Online</div>
    <?php endif; ?>
  </div>
  <div class="fl_l menu">

  </div>
</div>
<?php endforeach; ?>
