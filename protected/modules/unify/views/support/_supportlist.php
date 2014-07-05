<?php /** @var $support Support */ ?>
<?php $page = ($offset + Yii::app()->controller->module->supportsPerPage) / Yii::app()->controller->module->supportsPerPage ?>
<?php $added = false; ?>
<?php foreach ($supports as $support): ?>
  <div class="support_message_row"<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?>>
    <div class="support_message_author">
      <span id="support_author_<?php echo $support->id ?>"><?php echo $support->name ?></span> <span class="support_message_contact"><?php echo $support->email ?></span>
    </div>
    <div class="support_message_content">
      <?php echo nl2br($support->msg) ?>
    </div>
    <div class="support_message_bottom">
      <?php echo ActiveHtml::date($support->date) ?> |
      <span id="support_status_<?php echo $support->id ?>"><?php echo $support->status ?></span> |
      <a onclick="Support.answer(<?php echo $support->id ?>)">Ответить</a> |
      <a id="support_recharge_<?php echo $support->id ?>" onclick="Support.recharge(<?php echo $support->id ?>)">Изменить статус</a>
    </div>
  </div>
<?php endforeach; ?>