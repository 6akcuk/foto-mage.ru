<?php
/**
 * @var $dialog Dialog
 * @var $member DialogMember
 */
?>
<?php $page = ($offset + Yii::app()->controller->module->dialogsPerPage) / Yii::app()->controller->module->dialogsPerPage ?>
<?php $added = false; ?>
<?php foreach ($dialogs as $dialog): ?>
<div class="dialogs_row<?php if($dialog->lastMessage->isNew->req_link_id && $dialog->lastMessage->isNew->owner_id == Yii::app()->user->getId()) echo " dialogs_new_msg" ?>" id="im_dialog<?php echo $dialog->dialog_id ?>" onclick="Im.selectDialog(<?php echo $dialog->dialog_id ?>, event)" onmouseover="$(this).addClass('dialogs_row_over')" onmouseout="$(this).removeClass('dialogs_row_over')">
    <div class="dialogs_del_wrap">
        <div class="dialogs_del" rel="tooltip" title="Удалить диалог"></div>
    </div>
    <table class="dialogs_row_t">
        <tr>
            <td class="dialogs_photo">
            <?php if ($dialog->type == Dialog::TYPE_TET): ?>
            <?php $selfShow = 0; ?>
                <?php foreach ($dialog->members as $member): ?>
                <?php if ($member->member_id == Yii::app()->user->getId() && $selfShow == 0) { $selfShow++; continue; } ?>
                <?php $interlocutor = $member; ?>
                <?php echo ActiveHtml::link(
                    ($member->user->profile->photo != '0')
                        ? ActiveHtml::showUploadImage($member->user->profile->photo, 'c')
                        : '<img src="/images/camera_a.gif" />', '/id'. $member->member_id) ?>
                <?php endforeach; ?>
            <?php elseif ($dialog->type == Dialog::TYPE_CONFERENCE): ?>
                <?php foreach ($dialog->members as $idx => $member): ?>
                  <?php if ($idx > 3) break; ?>
                    <?php echo ActiveHtml::link(
                        '<div class="dialogs_inline_chatter"'. (($idx % 2) ? ' style="margin-right:0"' : '') .'>' .
                        (($member->user->profile->photo != '0')
                            ? ActiveHtml::showUploadImage($member->user->profile->photo, 'c', array('class' => 'dialogs_inline_chatter', 'width' => 33, 'height' => 33))
                            : '<img class="dialogs_inline_chatter" src="/images/camera_a.gif" width="33" height="33" />') .'</div>', '/id'. $member->member_id) ?>
                    <?php endforeach; ?>
            <?php endif; ?>
            </td>
            <td class="dialogs_info">
                <div class="dialogs_user">
                <?php if ($dialog->type == Dialog::TYPE_TET): ?>
                    <?php echo ActiveHtml::link($interlocutor->user->getDisplayName(), '/id'. $interlocutor->user->id) ?>
                <?php elseif ($dialog->type == Dialog::TYPE_CONFERENCE): ?>
                    <?php echo ActiveHtml::link($dialog->title, '/im?sel='. $dialog->dialog_id) ?>
                <?php endif; ?>
                </div>
                <?php if ($dialog->type == Dialog::TYPE_TET && $interlocutor->user->isOnline()): ?>
                <div class="dialogs_online">Online</div>
                <?php endif; ?>
                <div class="dialogs_date">
                    <?php if($dialog->lastMessage) echo ActiveHtml::date($dialog->lastMessage->creation_date, true, true) ?>
                </div>
            </td>
            <td class="dialogs_msg_contents">
                <div class="dialogs_msg_body<?php if ($dialog->lastMessage->isNew->req_link_id && $dialog->lastMessage->isNew->owner_id != Yii::app()->user->getId()) echo " dialogs_new_msg" ?> clearfix">
                <?php if($dialog->lastMessage): ?>
                    <?php if ($dialog->type == Dialog::TYPE_CONFERENCE || ($dialog->type == Dialog::TYPE_TET && $interlocutor->user->id != $dialog->leader_id)): ?>
                    <?php echo ($dialog->lastMessage->author->profile->photo)
                            ? ActiveHtml::showUploadImage($dialog->lastMessage->author->profile->photo, 'c', array('class' => 'left dialogs_inline_author'))
                            : '<img src="/images/camera_a.gif" class="left dialogs_inline_author" />' ?>
                    <?php endif; ?>
                    <div class="dialogs_msg_text wrapped left">
                        <?php echo nl2br($dialog->lastMessage->message) ?>
                    </div>
                <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>
</div>
<?php endforeach; ?>