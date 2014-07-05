<?php
/** @var $message DialogMessage */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerCssFile('/css/im.css');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

Yii::app()->getClientScript()->registerScriptFile('/js/mail.js');

Yii::app()->getClientScript()->registerCssFile('/css/photoview.css');
Yii::app()->getClientScript()->registerScriptFile('/js/photoview.js');

Yii::app()->getClientScript()->registerScriptFile('/js/jquery.cookie.js', null, 'after jquery-');

$this->pageTitle = Yii::app()->name .' - Сообщение от '. ActiveHtml::lex(2, $message->author->getDisplayName());
$delta = Yii::app()->controller->module->messagesPerPage;

$attaches = json_decode($message->attaches, true);

/**
 * Нарушение модели MVC
 * TODO: В представлении имеется запрос к модели
 */
if ($message->dialog->type == Dialog::TYPE_TET && $message->author_id == Yii::app()->user->getId()) {
  $member = DialogMember::model()->with('user.profile')->find('dialog_id = :id AND member_id != :mid', array(':id' => $message->dialog_id, ':mid' => Yii::app()->user->getId()));
  if (!$member) {
    $member = new DialogMember();
    $member->member_id = Yii::app()->user->getId();
    $member->user = Yii::app()->user->model;
  }
}
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Полученные', '/mail?act=inbox') ?>
  <?php echo ActiveHtml::link('Отправленные', '/mail?act=outbox') ?>
  <?php echo ActiveHtml::link('Просмотр сообщения', '#', array('class' => 'selected')) ?>
  <div class="fl_r">
    <?php echo ActiveHtml::link('Написать сообщение', '/mail?act=write') ?>
  </div>
</div>
<div class="mail_message" style="padding-top: 40px">
  <table cellpadding="0" cellspacing="0">
  <tbody>
    <tr>
      <td class="mail_envelope_photo_cell">
        <div class="mail_envelope_photo">
        <?php if (($message->dialog->type == Dialog::TYPE_TET && $message->author_id != Yii::app()->user->getId()) || ($message->dialog->type == Dialog::TYPE_CONFERENCE && $message->isIncome())): ?>
          <?php echo ActiveHtml::link($message->author->profile->getProfileImage(), '/id'. $message->author_id) ?>
        <?php elseif ($message->dialog->type == Dialog::TYPE_TET && $message->isOutgoing()): ?>
          <?php echo ActiveHtml::link($member->user->profile->getProfileImage(), '/id'. $member->member_id) ?>
        <?php elseif ($message->dialog->type == Dialog::TYPE_CONFERENCE && $message->isOutgoing()): ?>
          <?php echo ActiveHtml::link('<img src="/images/camera_a.gif" width="100" />', '/im?sel='. $message->dialog_id, array('onmousedown' => 'event.cancelBubble = true;')) ?>
        <?php endif; ?>
        </div>
      </td>
      <td>
        <h4>
          <div id="mail_envelope_actions" class="fl_r">
            <a id="mess<?php echo $message->message_id ?>_del" onclick="Mail.showMsgDelete(<?php echo $message->message_id ?>)">удалить</a>
          </div>
          <?php if ($message->dialog->type == Dialog::TYPE_TET): ?>
          Сообщение <?php echo ($message->isIncome()) ? 'от' : 'для' ?> <?php echo ($message->isIncome()) ? ActiveHtml::link(ActiveHtml::lex(2, $message->author->getDisplayName()), '/id'. $message->author_id) : ActiveHtml::link(ActiveHtml::lex(2, $member->user->getDisplayName()), '/id'. $member->member_id) ?>
          <?php elseif ($message->dialog->type == Dialog::TYPE_CONFERENCE): ?>
          <?php echo ActiveHtml::link($message->dialog->title, '/im?sel='. $message->dialog_id) ?>
          <?php endif; ?>
        </h4>
        <div class="mail_envelope_time">
          <?php echo ActiveHtml::date($message->creation_date, true, true) ?><?php if ($message->dialog->type == Dialog::TYPE_CONFERENCE): ?>,
          отправлено
            <?php if ($message->isIncome()): ?>
              Вам и еще <a onclick=""><?php echo Yii::t('mail', '{n} собеседнику|{n} собеседникам|{n} собеседникам', $message->dialog->membersNum - 2) ?></a>
            <?php else: ?>
              Вами <a onclick=""><?php echo Yii::t('mail', '{n} собеседнику|{n} собеседникам|{n} собеседникам', $message->dialog->membersNum - 1) ?></a>
            <?php endif; ?>
          <?php endif; ?>
        </div>
        <div class="mail_envelope_body">
          <?php echo nl2br($message->message) ?>
        </div>
        <?php if($message->photo || $message->document): ?>
        <div class="mail_envelope_attaches clear_fix">
          <?php
          if ($message->photo):
            $photos = json_decode($message->photo, true);

            $length = sizeof($photos);
            $list = array('items' => array(), 'count' => $length);
            ?>
            <?php foreach ($photos as $akey => $photo): ?>
              <?php $list['items'][] = $photo ?>
              <a class="fl_l" onclick="Photoview.show('mess<?php echo $message->message_id ?>', <?php echo $akey ?>)">
                <?php echo ActiveHtml::showUploadImage($photo, 'e'); ?>
              </a>
            <?php endforeach; ?>
            <script type="text/javascript">
              Photoview.list('mess<?php echo $message->message_id ?>', <?php echo json_encode($list) ?>);
            </script>
          <?php endif; ?>
        </div>
        <div class="mail_envelope_attaches clear_fix">
          <?php if ($message->document): ?>
            <?php $documents = json_decode($message->document, true); ?>
            <?php foreach ($documents as $document): ?>
            <div class="media_preview_document_wrap fl_l">
              <a target="_blank" class="medadd_h medadd_h_doc inl_bl" href="<?php echo ActiveHtml::getDocumentUrl($document) ?>">Документ</a>
              <div class="medadd_c medadd_c_doc">
                <a target="_blank" href="<?php echo ActiveHtml::getDocumentUrl($document) ?>"><?php echo $document[3] ?></a>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </td>
    </tr>
  </tbody>
  </table>
  <input type="hidden" name="dialog_id" value="<?php echo $message->dialog_id ?>" />
  <div class="mail_envelope_form" style="padding-top: 10px">
    <?php echo ActiveHtml::textArea('mail_message', '', array(
      'class' => 'text',
      'onkeypress' => 'onCtrlEnter(event, Mail.send)',
    )) ?>
  </div>
  <div id="mail_attaches" class="mail_post_attaches clear_fix"></div>
  <div class="mail_envelope_post clear_fix">
    <div class="fl_l">
      <div class="button_blue">
        <button id="send_button" onclick="Mail.send()"><?php echo ($message->isIncome()) ? 'Ответить' : 'Отправить' ?></button>
      </div>
    </div>
    <div id="mail_progress" class="left mail_post_progress">
      <img src="/images/upload.gif" />
    </div>
  </div>
</div>
<div id="mail_history">
  <a id="mail_history_open" onclick="Mail.showHistory(<?php echo $message->dialog_id ?>)">
    Показать историю сообщений
    <?php if ($message->dialog->type == Dialog::TYPE_TET): ?>
    с <?php echo ActiveHtml::lex(5, ($message->isIncome()) ? $message->author->profile->firstname : $member->user->profile->firstname) ?>
    <?php elseif ($message->dialog->type == Dialog::TYPE_CONFERENCE): ?>
    в беседе «<?php echo $message->dialog->title ?>»
    <?php endif; ?>
  </a>
</div>

<?php
$this->pageJS = <<<HTML
cur.uploadAction = 'http://cs1.foto-mage.ru/upload.php';

Mail.initSend();
HTML;
