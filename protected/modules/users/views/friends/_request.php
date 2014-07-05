<?php /** @var $people ProfileRelationship */ ?>
<?php $page = ($offset + Yii::app()->controller->module->friendsPerPage) / Yii::app()->controller->module->friendsPerPage ?>
<?php $added = false; ?>
<?php foreach ($peoples as $people): ?>
<div<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?> id="people<?php echo $people->friend->id ?>" class="people clear_fix">
  <div class="fl_l photo">
    <?php echo ($people->friend->profile->photo) ? ActiveHtml::showUploadImage($people->friend->profile->photo) : '<img src="/images/camera_a.gif" />' ?>
  </div>
  <div class="fl_l info request_info">
    <div class="labeled name">
      <?php echo ActiveHtml::link($people->friend->getDisplayName(), '/id'. $people->friend->id) ?>
    </div>
    <div class="labeled"><?php echo $people->friend->profile->city->name ?></div>
    <?php if ($people->friend->role->itemname != 'Пользователь'): ?>
    <div class="labeled"><?php echo $people->friend->role->itemname ?></div>
    <?php endif; ?>
    <?php if ($people->friend->isOnline()): ?>
    <div class="online">Online</div>
    <?php endif; ?>
    <div class="clear_fix">
      <?php if (Yii::app()->user->model->profile->isProfileRelationIncome($people)): ?>
      <div class="button_blue fl_l">
        <button onclick="return Profile.addFriend(this, <?php echo $people->friend->id ?>)">Добавить в друзья</button>
      </div>
      <?php if(isset($current)): ?><a onclick="return Profile.keepSubscriber(this, <?php echo $people->friend->id ?>)" class="fl_l" style="padding: 5px 0 0 10px">Оставить в подписчиках</a><?php endif; ?>
      <?php endif; ?>
      <?php if (Yii::app()->user->model->profile->isProfileRelationOutcome($people)): ?>
      <div class="button_blue">
        <button onclick="return Profile.deleteFriend(this, <?php echo $people->friend->id ?>)">Отменить заявку и отписаться</button>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endforeach; ?>
