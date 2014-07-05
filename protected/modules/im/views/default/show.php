<?php
/**
 * @var $dialog Dialog
 * @var $message DialogMessage
 * @var $member DialogMember
 */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerCssFile('/css/im.css');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

Yii::app()->getClientScript()->registerScriptFile('/js/im.js');

$this->pageTitle = Yii::app()->name .' - Просмотр диалога';
$delta = Yii::app()->controller->module->messagesPerPage;

$peer = null;
$members = array();
$selfCounter = 0;
foreach ($dialog->members as $member) {
    if ($selfCounter == 0 && $member->member_id == Yii::app()->user->getId()) {
        $selfCounter++;
        continue;
    }

    if ($dialog->type == Dialog::TYPE_TET) $peer = $member;
    $members[] = $member;
}

?>
<div id="im_nav">
    <div class="tabs">
        <?php echo ActiveHtml::link('Диалоги', '/im') ?>
        <?php echo ActiveHtml::link('Просмотр диалога', '/im?sel='. $dialog->dialog_id, array('class' => 'selected')) ?>
    </div>
    <div class="im_bar clearfix">
        <div class="summary_tab_sel left">
            <div class="summary_tab2">
                <div class="summary_tab3"><?php echo ($dialog->type == Dialog::TYPE_TET) ? $peer->user->getDisplayName() : $dialog->title ?></div>
            </div>
        </div>
        <div style="display: none">
            <?php $this->widget('Paginator', array(
            'url' => '/im?sel='. $dialog->dialog_id,
            'offset' => $offset,
            'offsets' => $offsets,
            'delta' => $delta,
            'nopages' => true,
        )); ?>
        </div>
    </div>
</div>
<div id="im_content">
    <div id="im_rows_wrap" style="height: auto">
        <div id="im_rows" style="height: 100px">
            <div class="im_peer_rows">
                <table class="im_log_t" id="im_log<?php echo $dialog->dialog_id ?>">
                    <?php echo $this->renderPartial('_im', array('messages' => $messages, 'offset' => $offset), true) ?>
                </table>
                <div class="im_none" id="im_none<?php echo $dialog->dialog_id ?>" style="display:none">Здесь будет выводиться история переписки</div>
                <div class="im_typing_wrap">
                    <div class="im_lastact"></div>
                </div>
            </div>
         </div>
    </div>
</div>
<div id="im_controls_wrap">
    <div id="im_bottoms_sh"></div>
    <div id="im_peer_controls_wrap">
        <div id="im_peer_controls">
            <table>
            <tr>
                <td id="im_user_holder">
                    <?php echo (Yii::app()->user->model->profile->photo)
                        ? ActiveHtml::showUploadImage(Yii::app()->user->model->profile->photo, 'c', array('class' => 'im_user_holder'))
                        : '<img src="/images/camera_a.gif" class="im_user_holder" />'?>
                </td>
                <td class="im_write_form">
                    <div id="im_texts">
                        <?php echo ActiveHtml::smartTextarea('im_text', '', array('class' => 'im_editable')) ?>
                    </div>
                    <div id="im_send_wrap" class="clearfix">
                        <a id="im_send" class="left button" onclick="return Im.send(<?php echo $dialog->dialog_id ?>)">Отправить</a>
                        <div id="im_progress" class="left form_progress" style="display:none"></div>
                    </div>
                </td>
                <td id="im_peer_holders">
                <?php if ($dialog->type == Dialog::TYPE_TET): ?>
                    <div class="im_peer_holder fl_l">
                        <div class="im_photo_holder">
                            <?php echo ActiveHtml::link(
                                ($peer->user->profile->photo)
                                    ? ActiveHtml::showUploadImage($peer->user->profile->photo, 'c')
                                    : '<img src="/images/camera_a.gif" />', '/id'. $peer->member_id, array('target' => '_blank')) ?>
                        </div>
                        <div class="im_status_holder" id="im_status_holder">
                        <?php if ($peer->user->isOnline()) echo "online" ?>
                        </div>
                    </div>
                <?php elseif ($dialog->type == Dialog::TYPE_CONFERENCE): ?>
                <?php foreach ($members as $member): ?>
                    <div class="left im_peer_mini_holder">
                        <div class="im_photo_holder">
                            <?php
                            echo ActiveHtml::link(
                                (($member->user->profile->photo)
                                    ? ActiveHtml::showUploadImage($member->user->profile->photo, 'c')
                                    : '<img src="/images/camera_a.gif" />') .
                                (($member->user->isOnline()) ? '<div class="im_status_online"></div>' : ''),
                                '/id'. $member->member_id,
                                array('target' => '_blank')
                            )
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php endif; ?>
                </td>
            </tr>
            </table>
        </div>
        <div id="im_footer_filler"></div>
    </div>
</div>

<script type="text/javascript">
Im.setup(<?php echo $dialog->dialog_id ?>);
</script>