<?php

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerCssFile('/css/im.css');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

Yii::app()->getClientScript()->registerScriptFile('/js/mail.js');

Yii::app()->getClientScript()->registerCssFile('/css/photoview.css');
Yii::app()->getClientScript()->registerScriptFile('/js/photoview.js');
Yii::app()->getClientScript()->registerScriptFile('/js/jquery.cookie.js', null, 'after jquery-');

$this->pageTitle = Yii::app()->name .' - Новое сообщение';

$friendsJS = array();
foreach ($friends as $friend) {
  if ($guest && $guest->id == $friend->friend->id)
    continue;

  $friendsJS[] = "[". $friend->friend->id .",'". $friend->friend->getDisplayName() ."',false,false,0,'". $friend->friend->profile->city->name ."','". ActiveHtml::getPhotoUrl($friend->friend->profile->photo, 'c') ."']";
  //$friendsJS[] = "'". $friend->friend->id ."': {img: '". (($friend->friend->profile->photo) ? ActiveHtml::getImageUrl($friend->friend->profile->photo, 'a') : 'http://spmix.ru/images/camera_a.gif') ."', text: '". $friend->friend->getDisplayName() ."', sub: '". $friend->friend->profile->city->name ."'}";
}

//$friendsJS[] = "'". Yii::app()->user->getId() ."': {private: true, img: '". ((Yii::app()->user->model->profile->photo) ? ActiveHtml::getImageUrl(Yii::app()->user->model->profile->photo, 'a') : 'http://spmix.ru/images/camera_a.gif') ."', text: '". Yii::app()->user->model->getDisplayName() ."', sub: '". Yii::app()->user->model->profile->city->name ."'}";
if ($guest)
  $friendsJS[] = "[". $guest->id .",'". $guest->getDisplayName() ."',false,false,0,'". $guest->profile->city->name ."','". ActiveHtml::getPhotoUrl($guest->profile->photo, 'c') ."']";

$friendsJS = implode(",", $friendsJS);

?>
<div class="tabs">
  <?php echo ActiveHtml::link('Полученные', '/mail?act=inbox') ?>
  <?php echo ActiveHtml::link('Отправленные', '/mail?act=outbox') ?>
  <?php echo ActiveHtml::link('Новое сообщение', '/mail?act=write', array('class' => 'selected')) ?>
</div>
<div class="mail_message">
  <h4 class="mail_write_header">Получатель</h4>
  <div>
    <?php echo ActiveHtml::hiddenField('recipient', ($guest) ? $guest->id : '') ?>
  </div>
  <div id="im_theme" class="row" style="display:none">
    <h4 class="mail_write_header">Тема</h4>
    <?php echo ActiveHtml::inputPlaceholder('Mail[title]', '', array('style' => 'width:324px')) ?>
  </div>
  <h4 class="mail_write_header">Сообщение</h4>
  <div class="mail_envelope_form">
    <?php echo ActiveHtml::textArea('mail_message', '', array(
      'class' => 'text',
      'onkeypress' => 'onCtrlEnter(event, Mail.write)',
    )) ?>
  </div>
  <div id="mail_attaches" class="mail_post_attaches clear_fix"></div>
  <div class="mail_envelope_post clear_fix">
    <div class="fl_l">
      <div class="button_blue">
        <button id="send_button" onclick="Mail.write()">Отправить</button>
      </div>
    </div>
    <div id="mail_progress" class="fl_l mail_post_progress">
      <img src="/images/upload.gif" />
    </div>
  </div>
</div>

<?php
$this->pageJS = <<<HTML
cur.uploadAction = 'http://cs1.foto-mage.ru/upload.php';

Mail.initWrite({friends: [${friendsJS}]});
HTML;
