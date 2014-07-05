<div class="news_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'post_form'
  )); ?>
  <div id="news_result"></div>
  <div id="news_error" class="error"></div>
  <div class="news_form_param">
    <div class="news_form_header"><?php echo $ads->getAttributeLabel('city_id') ?></div>
    <div class="news_form_param"><?php echo ActiveHtml::hiddenField('city_id', $ads->city_id) ?></div>
  </div>
  <div class="news_form_param">
    <div class="news_form_header"><?php echo $ads->getAttributeLabel('banner') ?></div>
    <div class="news_form_param"><?php echo ActiveHtml::hiddenField('banner', $ads->banner) ?></div>
  </div>
  <div class="news_form_param">
    <div class="news_form_header"><?php echo $ads->getAttributeLabel('weight') ?></div>
    <div class="news_form_param"><?php echo ActiveHtml::textField('weight', $ads->weight, array('class' => 'text')) ?></div>
  </div>
  <?php $this->endWidget(); ?>
</div>