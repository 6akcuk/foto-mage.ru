<?php
/** @var EventType $type
 * @var ActiveForm $form
 */

$this->pageTitle = 'Добавить новый тип событий';
?>
<div class="org_type_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'type_form'
  )); ?>
  <div id="org_eventtype_result"></div>
  <div id="org_eventtype_error" class="error"></div>
  <div class="org_type_header"><?php echo $type->getAttributeLabel('type_name') ?></div>
  <?php echo ActiveHtml::textField('type_name', $type->type_name, array('class' => 'text')) ?>
  <?php echo ActiveHtml::hiddenField('type_today', $type->type_today, array('class' => 'text')) ?>
  <?php $this->endWidget(); ?>
</div>
<?php
$this->pageJS = <<<HTML
cur.uiTodayChk = new Checkbox('type_today', {label: 'Событие отображается в разделе "Сегодня в городе"'});
HTML;
