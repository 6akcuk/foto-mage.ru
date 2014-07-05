<?php
/**
 * @var $friend ProfileRelationship
 * @var $guest User
 */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerCssFile('/css/im.css');

Yii::app()->getClientScript()->registerScriptFile('/js/im.js');

$this->pageTitle = Yii::app()->name .' - Новое сообщение';

$friendsJS = array();
foreach ($friends as $friend) {
    $friendsJS[] = "'". $friend->friend->id ."': {img: '". (($friend->friend->profile->photo) ? ActiveHtml::getImageUrl($friend->friend->profile->photo, 'a') : 'http://spmix.ru/images/camera_a.gif') ."', text: '". $friend->friend->getDisplayName() ."', sub: '". $friend->friend->profile->city->name ."'}";
}

$friendsJS[] = "'". Yii::app()->user->getId() ."': {private: true, img: '". ((Yii::app()->user->model->profile->photo) ? ActiveHtml::getImageUrl(Yii::app()->user->model->profile->photo, 'a') : 'http://spmix.ru/images/camera_a.gif') ."', text: '". Yii::app()->user->model->getDisplayName() ."', sub: '". Yii::app()->user->model->profile->city->name ."'}";

?>
<div class="tabs">
    <?php echo ActiveHtml::link('Диалоги', '/im') ?>
    <?php echo ActiveHtml::link('Новое сообщение', '/im?sel=-1', array('class' => 'selected')) ?>
</div>
<div id="imform" class="wrap2">
    <div class="row">
        <h5>Получатель</h5>
        <div id="im_wdd" class="wdd clearfix" onclick="WideDropdown.show('im_wdd', event)">
            <div class="wdd_lwrap" style="width: 420px">
                <div class="wdd_list"></div>
            </div>
            <div class="right wdd_arrow"></div>
            <div class="wdd_bubbles"></div>
            <div class="wdd_add left" style="display:none">
                <div class="wdd_add2">
                    <table>
                        <tr>
                            <td>
                                <div class="wdd_add3">
                                    <nobr>Добавить</nobr>
                                </div>
                            </td>
                            <td>
                                <div class="wdd_add_plus"></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
            <?php echo ActiveHtml::inputPlaceholder(
                '',
                '',
                array(
                    'class' => 'left wdd_text',
                    'placeholder' => 'Введите имя друга',
                    'onfocus' => "WideDropdown.setFocused('im_wdd', event)",
                    'onblur' => "WideDropdown.setUnfocused('im_wdd', event)",
                )
            ) ?>
<script type="text/javascript">
WideDropdown.addList('im_wdd', {<?php echo implode(', ', $friendsJS) ?>});
<?php if($guest): ?>WideDropdown.addBubbles('im_wdd', {'<?php echo $guest->id ?>': {img: '<?php echo (($guest->profile->photo) ? ActiveHtml::getImageUrl($guest->profile->photo, 'a') : 'http://spmix.ru/images/camera_a.gif') ?>', text: '<?php echo $guest->getDisplayName() ?>', sub: '<? echo $guest->profile->city->name ?>'}});<?php endif; ?>
</script>
        </div>
    </div>
    <div id="im_theme" class="row" style="display:none">
        <h5>Тема</h5>
        <?php echo ActiveHtml::inputPlaceholder('Im[title]', '') ?>
    </div>
    <div class="row">
        <h5>Сообщение</h5>
        <?php echo ActiveHtml::smartTextarea('Im[message]', '') ?>
    </div>
    <div class="row">
        <a id="im_send" class="left button" onclick="return Im.sendMessage()">Отправить</a>
    </div>
</div>
<script type="text/javascript">
if (!A.wddOnSelect) A.wddOnSelect = {};
A.wddOnSelect['im_wdd'] = function() {
    if (WideDropdown.countBubbleSize('im_wdd') > 1) $('#im_theme').show();
};
if (!A.wddOnDeselect) A.wddOnDeselect = {};
A.wddOnDeselect['im_wdd'] = function() {
    if (WideDropdown.countBubbleSize('im_wdd') <= 1) $('#im_theme').hide();
};
</script>