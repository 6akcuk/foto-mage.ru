<?php
/**
 * @var $message DialogMessage
 */

?>
<?php foreach ($messages as $message): ?>
<tr class="<?php if ($message->isNew) echo "new_msg" ?>" read="<?php if (!$message->isNew) echo "1" ?>" id="mess<?php echo $message->message_id ?>" onmousedown="return nav.go('/mail?act=show&id=<?php echo $message->message_id ?>', event, {})">
  <td class="mail_check" onclick="mail.select()" onmousedown="event.cancelBubble = true;">
    <input type="checkbox" name="checkMsg[]" value="<?php echo $message->message_id ?>" />
  </td>
  <td class="mail_photo">
  <?php if ($message->dialog->type == Dialog::TYPE_TET || ($message->dialog->type == Dialog::TYPE_CONFERENCE && $message->isIncome())): ?>
    <?php echo ActiveHtml::link($message->author->profile->getProfileImage('c'), '/id'. $message->author_id, array('onmousedown' => 'event.cancelBubble = true;')) ?>
  <?php elseif ($message->dialog->type == Dialog::TYPE_CONFERENCE && $message->isOutgoing()): ?>
    <?php echo ActiveHtml::link('<img src="/images/camera_a.gif" width="70" />', '/im?sel='. $message->dialog_id, array('onmousedown' => 'event.cancelBubble = true;')) ?>
  <?php endif; ?>
  </td>
  <td class="mail_from">
    <div class="name wrapped">
    <?php if ($message->dialog->type == Dialog::TYPE_TET || ($message->dialog->type == Dialog::TYPE_CONFERENCE && $message->isIncome())): ?>
      <?php echo ActiveHtml::link($message->author->getDisplayName(), '/id'. $message->author_id, array('onmousedown' => 'event.cancelBubble = true;')) ?>
    <?php elseif ($message->dialog->type == Dialog::TYPE_CONFERENCE && $message->isOutgoing()): ?>
      <?php echo ActiveHtml::link($message->dialog->title, '/im?sel='. $message->dialog_id, array('onmousedown' => 'event.cancelBubble = true;')) ?>
    <?php endif; ?>
    </div>
    <?php if ($message->author->isOnline()): ?>
    <div class="online">Online</div>
    <?php endif; ?>
    <div class="date">
      <?php echo ActiveHtml::date($message->creation_date, true, true) ?>
    </div>
  </td>
  <td class="mail_contents">
    <div class="mail_topic">
      <?php echo ActiveHtml::link(($message->dialog->type == Dialog::TYPE_TET) ? ' ... ' : ''. $message->dialog->title, '/mail?act=show&id='. $message->message_id) ?>
    </div>
    <div class="mail_body">
      <?php $body = mb_substr($message->message, 0, 100, 'utf-8') ?>
      <?php if (mb_strlen($message->message, 'utf-8') > 100) $body .= '...'; ?>
      <?php
        if ($message->photo) {
          $body .= '<div class="mail_msg_attach"><div class="im_attach_photo"></div> Фотография</div>';
        }
      ?>
      <?php echo ActiveHtml::link($body, '/mail?act=show&id='. $message->message_id) ?>
    </div>
  </td>
  <td class="mail_actions">
  <?php if ($message->dialog->type == Dialog::TYPE_TET): ?>
    <a id="mess<?php echo $message->message_id ?>_del" href="#" onclick="Mail.deleteMsg(<?php echo $message->message_id ?>); return false;" onmousedown="event.cancelBubble = true;">Удалить</a>
  <?php endif; ?>
  </td>
</tr>
<?php endforeach; ?>