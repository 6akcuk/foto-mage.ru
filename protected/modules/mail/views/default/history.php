<?php
/**
 * Created by JetBrains PhpStorm.
 * User: denis
 * Date: 04.04.13
 * Time: 12:07
 * To change this template use File | Settings | File Templates.
 */

$delta = Yii::app()->getModule('mail')->messagesPerPage;
?>
<div style="display: none">
  <?php $this->widget('Paginator', array(
    'url' => '/mail?act=history&id='. $id,
    'forceUrl' => 1,
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
    'nopages' => true,
  )); ?>
</div>
<h4 class="new_header">История сообщений</h4>
<table id="mail_history_t" cellspacing="0" cellpadding="0" rel="pagination">
  <?php $this->renderPartial('_history', array('messages' => $messages, 'offset' => $offset)) ?>
</table>
<? if ($offset + $delta < $offsets && $offsets > $delta): ?><a id="pg_more" class="pg_more" onclick="Paginator.showMore()">Еще сообщения</a><? endif; ?>