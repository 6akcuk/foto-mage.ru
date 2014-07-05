<?php
$this->pageTitle = Yii::app()->name . ' - Техническая поддержка';

Yii::app()->getClientScript()->registerCssFile('/css/support.css');
Yii::app()->getClientScript()->registerScriptFile('/js/support.js');

$delta = Yii::app()->getModule('unify')->supportsPerPage;
?>
  <div class="support_search_wrap clear_fix">
    <div rel="filters" class="fl_l">
      <?php echo ActiveHtml::textField('c[q]', (isset($c['q'])) ? $c['q'] : '', array('class' => 'text', 'placeholder' => 'Начните вводить текст сообщения')) ?>
    </div>
    <div class="fl_l" style="margin-left: 10px">
      <input type="hidden" id="c[status]" name="c[status]" value="<?php echo (isset($c['status'])) ? $c['status'] : '' ?>">
    </div>
    <div class="fl_r">
    </div>
  </div>
  <div class="summary_wrap">
    <?php $this->renderPartial('//layouts/pages', array(
      'url' => '/unify/support/index',
      'offset' => $offset,
      'offsets' => $offsets,
      'delta' => $delta,
    )) ?>
    <div class="fl_r progress"></div>
    <div class="summary"><?php echo Yii::t('app', '{n} сообщение|{n} сообщения|{n} сообщений', $offsets) ?></div>
  </div>
  <div class="city_wrap">
    <?php if ($supports): ?>
      <?php echo $this->renderPartial('_supportlist', array('supports' => $supports, 'offset' => $offset)) ?>
    <?php else: ?>
      <div id="no_results">В техническую поддержку не было еще ни одного обращения</div>
    <?php endif; ?>
  </div>
<?php
$this->pageJS = <<<HTML
Support.init();
HTML;
