<?php
/** @var $people User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerCssFile('/css/im.css');

Yii::app()->getClientScript()->registerScriptFile('/js/mail.js');

$this->pageTitle = Yii::app()->name .' - Полученные сообщения';
$delta = Yii::app()->controller->module->messagesPerPage;

$url_suffix = array();
if (sizeof($c) > 0) {
  foreach ($c as $c_key => $c_value) {
    $url_suffix[] = "c[". $c_key ."]=". urlencode($c_value);
  }
}
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Полученные', '/mail?act=inbox', array('class' => 'selected')) ?>
  <?php echo ActiveHtml::link('Отправленные', '/mail?act=outbox') ?>
  <div class="fl_r">
    <?php echo ActiveHtml::link('Написать сообщение', '/mail?act=write') ?>
  </div>
</div>
<div class="im_bar clear_fix">
  <div class="fl_l" style="padding-top: 6px">
    Выделить:
    <a onclick="Mail.selectAll()">все</a>,
    <a onclick="Mail.selectReaded()">прочитанные</a>,
    <a onclick="Mail.selectNew()">новые</a>
  </div>
  <div id="mail_actions" style="display: none" class="fl_r">
    <div class="button_blue">
      <button onclick="Mail.deleteSelected(this)">Удалить</button>
    </div>
    <div class="button_blue">
      <button onclick="Mail.markAsReaded(this)">Отметить как прочитанные</button>
    </div>
    <div class="button_blue">
      <button onclick="Mail.markAsNew(this)">Отметить как новые</button>
    </div>
  </div>
  <div id="mail_search" rel="filters" class="fl_r">
    <?php echo ActiveHtml::textField('c[msg]', (isset($c['msg'])) ? $c['msg'] : '', array('class' => 'text', 'placeholder' => 'Поиск сообщений')) ?>
  </div>
</div>
<div class="summary_wrap">
  <?php $this->renderPartial('//layouts/pages', array(
    'url' => '/mail'. ((sizeof($url_suffix) > 0) ? '?'. implode('&', $url_suffix) : ''),
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
  )) ?>
  <div class="fl_r progress"></div>
  <div class="summary"><?php echo Yii::t('user', 'Вы получили {n} сообщение|Вы получили {n} сообщения|Вы получили {n} сообщений', $offsets) ?></span></div>
</div>
<table id="messages" rel="pagination">
<?php if ($messages): ?>
    <?php $this->renderPartial('_message', array('messages' => $messages, 'offset' => $offset)) ?>
  <?php else: ?>
  <tr>
    <td><div class="empty">Здесь будут отображаться Ваши личные сообщения</div></td>
  </tr>
<?php endif; ?>
</table>

<?php
$this->pageJS = <<<HTML
Mail.initMailList();
HTML;
