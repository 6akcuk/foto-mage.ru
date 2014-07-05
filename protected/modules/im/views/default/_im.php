<?php
/**
 * @var $message DialogMessage
 * @var $prev DialogMessage
*/

?>
<?php $prev = null; ?>
<?php foreach ($messages as $message): ?>
<?php $attaches = json_decode($message->attaches, true); ?>
<tr id="mess<?php echo $message->message_id ?>"
    class="<?php echo ($message->author_id == Yii::app()->user->getId()) ? 'im_out' : 'im_in'; ?> im_msg_from<?php echo $message->author_id ?> <?php if($message->isNewIn || $message->isNewOut) echo "im_new_msg" ?> <?php if ($prev && $prev->author_id == $message->author_id && (strtotime($message->creation_date) - strtotime($prev->creation_date)) < Yii::app()->getModule('im')->addRowInterval): ?>im_add_row<?php endif; ?>"
    date="<?php echo strtotime($message->creation_date) ?>"
    onmouseover="Im.setLogState(1, <?php echo $message->message_id ?>)"
    onmouseout="Im.setLogState(0, <?php echo $message->message_id ?>)"
    onclick="Im.checkLog(<?php echo $message->message_id ?>)"
>
    <td class="im_log_act">
        <div id="ma<?php echo $message->message_id ?>" class="im_log_check_wrap" style="visibility: hidden;">
            <div class="im_log_check" id="mess_check<?php echo $message->message_id ?>"></div>
        </div>
    </td>
    <td class="im_log_author">
        <div class="im_log_author_chat_thumb">
            <?php echo ActiveHtml::link(
                ($message->author->profile->photo)
                    ? ActiveHtml::showUploadImage($message->author->profile->photo, 'c')
                    : '<img src="/images/camera_a.gif" />',
                '/id'. $message->author_id, array('target' => '_blank')) ?>
        </div>
    </td>
    <td class="im_log_body">
        <div class="wrapped">
            <div class="im_log_author_chat_name">
                <?php echo ActiveHtml::link($message->author->profile->firstname, '/id'. $message->author_id, array('target' => '_blank')) ?>
            </div>
            <?php echo nl2br($message->message) ?>
            <div class="im_attaches">
            <?php if (sizeof($attaches) == 999): ?>
            <?php foreach ($attaches as $attache): ?>
                <?php if ($attache['type'] == 'purchase_edit'): ?>
                <a href="/purchase<?php echo $attache['purchase_id'] ?>/edit" target="_blank" onclick="return nav.go(this, null)">
                    <span class="iconify_link_a"></span>
                    <?php echo $attache['name'] ?>
                </a>
                <?php endif; ?>
            <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
    </td>
    <td class="im_log_date">
        <?php $date = date("d.m.y", strtotime($message->creation_date)); ?>
        <?php $today = date("d.m.y"); ?>
        <a class="im_date_link"<?php if ($today != $date): ?> rel="tooltip" title="Отправлено в <?php echo date("H:i", strtotime($message->creation_date)) ?>"<?php endif; ?>>
            <?php echo ($today == $date) ? date("H:i:s", strtotime($message->creation_date)) : $date ?>
        </a>
    </td>
    <td class="im_log_rspacer"></td>
</tr>
<?php $prev = $message; ?>
<?php endforeach; ?>