<?php
/**
 * @var $userinfo User
 * @var $friend ProfileRelationship
 */
$title = Yii::app()->name;

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerScriptFile('/js/profile.js');

if ($userinfo)
  $title .= ' - '. $userinfo->getDisplayName();

$this->pageTitle = $title;
?>
<div class="profile-header clear_fix">
  <span class="fl_l username">
    <?php echo $userinfo->getDisplayName(); ?>
  </span>
  <?php if ($userinfo->role->itemname == 'Организатор'): ?><span class="left userspecial">(Организатор)</span><?php endif; ?>
  <?php if ($userinfo->role->itemname == 'Модератор'): ?><span class="left userspecial">(Модератор)</span><?php endif; ?>
  <?php if ($userinfo->role->itemname == 'Администратор'): ?><span class="left userspecial">(Администратор)</span><?php endif; ?>
  <span class="fl_r useronline">
    <?php
    if ($userinfo->isOnline()) {
      echo "Online";
    }
    else {
      echo "был". (($userinfo->profile->gender == 'Female') ? "а" : "") ." в сети ". ActiveHtml::timeback($userinfo->lastvisit);
    }
    ?>
  </span>
</div>
<div class="profile-columns clear_fix">
  <div class="fl_l profile-left">
    <div class="profile-photo">
      <?php $photo = json_decode($userinfo->profile->photo, true); ?>
      <?php if (is_array($photo) && sizeof($photo)): ?>
        <?php echo ActiveHtml::showUploadImage($userinfo->profile->photo, 'a') ?>
      <?php else: ?>
        <img src="/images/camera_a.gif" alt="" />
      <?php endif; ?>
    </div>
    <?php if ($userinfo->id != Yii::app()->user->getId()): ?>
    <div class="module profile-socials">
      <div id="message-status">
        <div class="button_wide button_blue">
          <button onclick="return nav.go('/write<?php echo $userinfo->id ?>', event)">Отправить сообщение</button>
        </div>
      </div>
      <?php $relationship = $userinfo->profile->getProfileRelation(); ?>
      <div id="friend-status">
      <?php
        if ($relationship == null ||
          ($relationship->rel_type == ProfileRelationship::TYPE_INCOME && $relationship->from_id == Yii::app()->user->getId()) ||
          ($relationship->rel_type == ProfileRelationship::TYPE_OUTCOME && $relationship->from_id != Yii::app()->user->getId())): ?>
        <div class="button_wide button_blue">
          <button onclick="return Profile.addFriend(this, <?php echo $userinfo->id ?>)">Добавить в друзья</button>
        </div>
      <?php endif; ?>
      <?php if ($relationship != null): ?>
        <?php if ($relationship->rel_type == ProfileRelationship::TYPE_FRIENDS): ?>
          <div class="social-status"><?php echo $userinfo->getDisplayName() ?> у Вас в друзьях</div>
        <?php elseif (Yii::app()->user->model->profile->isProfileRelationIncome($relationship)): ?>
          <div class="social-status"><?php echo $userinfo->getDisplayName() ?> подписан<?php echo ($userinfo->profile->gender == 'Female') ? "а" : "" ?> на Вас</div>
        <?php elseif (Yii::app()->user->model->profile->isProfileRelationOutcome($relationship)): ?>
          <div class="social-status">Вы отправили заявку</div>
        <?php endif; ?>
      <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    <div class="module profile-num-menu"></div>
    <div class="module">
      <a href="/friends?id=<?php echo $userinfo->id ?>" onclick="return nav.go(this, event, {noback: false})" class="module-header">
        <div class="header-top">
          Друзья
        </div>
        <div class="header-bottom">
          <?php echo Yii::t('user', '{n} друг|{n} друга|{n} друзей', $friendsNum) ?>
        </div>
      </a>
    </div>
    <div class="module-body">
    <?php if ($friends): ?>
      <?php $fcnt = 0; ?>
      <?php foreach ($friends as $friend): ?>
        <?php $fcnt++; ?>
        <?php if ($fcnt > 6) break; ?>
        <?php if ($fcnt == 1 || $fcnt == 4): ?>
          <div class="clear_fix people_row">
        <?php endif; ?>
          <div class="fl_l people_cell">
            <?php echo ActiveHtml::link($friend->friend->profile->getProfileImage('c'), '/id'. $friend->friend->id, array('class' => 'ava')) ?>
            <div class="people_name">
            <?php echo ActiveHtml::link($friend->friend->profile->firstname .'<br/>'. $friend->friend->profile->lastname, '/id'. $friend->friend->id) ?>
            </div>
          </div>
        <?php if ($fcnt == 3 || $fcnt == 6): ?>
        </div>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if ($fcnt < 3 || ($fcnt > 3 && $fcnt < 6)): ?></div><?php endif; ?>
    <?php endif; ?>
    </div>
    <?php if ($friendsOnlineNum > 0): ?>
    <div class="module">
      <a href="/friends?id=<?php echo $userinfo->id ?>&section=online" onclick="return nav.go(this, event, {noback: false})" class="module-header">
        <div class="header-top">
          Друзья онлайн
        </div>
        <div class="header-bottom">
          <?php echo Yii::t('user', '{n} друг|{n} друга|{n} друзей', $friendsOnlineNum) ?>
        </div>
      </a>
    </div>
    <div class="module-body">
      <?php if ($friendsOnline): ?>
      <?php $fcnt = 0; ?>
      <?php foreach ($friendsOnline as $friend): ?>
        <?php $fcnt++; ?>
        <?php if ($fcnt > 6) break; ?>
        <?php if ($fcnt == 1 || $fcnt == 4): ?>
          <div class="clear_fix people_row">
        <?php endif; ?>
        <div class="fl_l people_cell">
          <?php echo ActiveHtml::link($friend->friend->profile->getProfileImage('c'), '/id'. $friend->friend->id, array('class' => 'ava')) ?>
          <div class="people_name">
            <?php echo ActiveHtml::link($friend->friend->profile->firstname .'<br/>'. $friend->friend->profile->lastname, '/id'. $friend->friend->id) ?>
          </div>
        </div>
        <?php if ($fcnt == 3 || $fcnt == 6): ?>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
      <?php if ($fcnt < 3 || ($fcnt > 3 && $fcnt < 6)): ?></div><?php endif; ?>
    <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
  <div class="fl_l profile-right">
    <?php if (($userinfo->id != Yii::app()->user->getId() && $userinfo->profile->status) || (Yii::app()->user->getId() == $userinfo->id)): ?>
    <div class="profile-info profile-status-container">
      <?php if (Yii::app()->user->getId() == $userinfo->id): ?>
      <a id="profile-status" class="profile-status-change" onclick="Profile.showStatusEditor(this)">
      <?php endif; ?>
        <?php echo ($userinfo->profile->status) ?: 'Изменить статус' ?>
      <?php if (Yii::app()->user->getId() == $userinfo->id): ?>
      </a>
      <div id="profile-status-editor">
        <input type="text" name="profile_status" value="" style="width:340px" />
        <a class="button" onclick="Profile.saveStatus()">Сохранить</a>
      </div>
      <?php endif; ?>
    </div>
      <?php endif; ?>
    <div class="profile-info">
      <?php if ($userinfo->profile->city): ?>
        <div class="clear_fix">
          <div class="label fl_l">
            Город:
          </div>
          <div class="labeled fl_l">
            <?php echo ActiveHtml::link($userinfo->profile->city->name, '/search?c[section]=people&c[city_id]='. $userinfo->profile->city_id) ?>
          </div>
        </div>
      <?php endif; ?>
      <?php if (Yii::app()->user->checkAccess('global.phoneView')): ?>
      <div class="clear_fix miniblock">
        <div class="label fl_l">
          Телефон:
        </div>
        <div class="labeled fl_l">
          <?php echo $userinfo->profile->phone ?>
        </div>
      </div>
      <?php endif; ?>
      <div class="clear_fix miniblock">
        <div class="label fl_l">
          Зарегистрирован<?php echo (($userinfo->profile->gender == 'Female') ? "а" : "") ?>:
        </div>
        <div class="labeled fl_l">
          <?php echo ActiveHtml::link(ActiveHtml::date($userinfo->regdate), '/search?c[section]=people&c[regdate]='. $userinfo->regdate) ?>
        </div>
      </div>
      <div class="module" style="margin-top: 10px">
        <a class="module-header">
          <div class="header-top">
            О себе
          </div>
        </a>
        <div class="module-body">
          <?php echo nl2br($userinfo->profile->about) ?>
        </div>
      </div>
    </div>
  </div>
</div>