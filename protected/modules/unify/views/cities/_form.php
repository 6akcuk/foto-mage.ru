<div class="city_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'post_form'
  )); ?>
  <div id="city_result"></div>
  <div id="city_error" class="error"></div>
  <div class="city_form_param">
    <div class="city_form_header"><?php echo $city->getAttributeLabel('name') ?></div>
    <?php echo ActiveHtml::textField('name', $city->name, array('class' => 'text')) ?>
  </div>
  <div class="city_form_param">
    <div class="city_form_header"><?php echo $city->getAttributeLabel('timezone') ?></div>
    <?php echo ActiveHtml::hiddenField('timezone', $city->timezone) ?>
  </div>
  <div class="city_form_param">
    <?php echo ActiveHtml::hiddenField('published', $city->published) ?>
  </div>
  <?php $this->endWidget(); ?>
</div>