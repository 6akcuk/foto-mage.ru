<?php
/**
 * @var $message DialogMessage
 */

?>
<?php foreach ($messages as $message): ?>
<tr id="mess<?php echo $message->message_id ?>" class="<?php echo ($message->author_id == Yii::app()->user->getId()) ? 'mail_outgoing' : 'mail_incoming' ?>">
  <td class="mail_history_author">
    <?php echo ActiveHtml::link($message->author->profile->firstname, '/id'. $message->author_id) ?>
  </td>
  <td class="mail_history_body">
    <div style="width:335px">
      <?php echo nl2br($message->message) ?>
    </div>
  </td>
  <td class="mail_history_date">
    <?php echo ActiveHtml::link(date("d.m.y", strtotime($message->creation_date)), '/mail?act=show&id='. $message->message_id) ?>
  </td>
  <td class="mail_history_act">
    <a class="mail_history_link" id="mess_del<?php echo $message->message_id ?>" onclick="mail.deleteHistMsg(<?php echo $message->message_id ?>)">удалить</a>
  </td>
</tr>
<?php endforeach; ?>