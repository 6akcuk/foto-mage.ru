<div class="news_form_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'post_form'
  )); ?>
  <div id="news_result"></div>
  <div id="news_error" class="error"></div>
  <div class="news_form_cols clear_fix">
    <div class="fl_l upload_wrap news_form_image_wrap">
      <?php echo ActiveHtml::hiddenField('facephoto', $post->facephoto) ?>
    </div>
    <div class="fl_l news_form_param">
      <div class="news_form_header"><?php echo $post->getAttributeLabel('city_id') ?></div>
      <div class="news_form_dropdown"><?php echo ActiveHtml::hiddenField('city_id', $post->city_id) ?></div>
    </div>
    <div class="fl_l news_form_param">
      <div class="news_form_header"><?php echo $post->getAttributeLabel('title') ?></div>
      <?php echo ActiveHtml::textField('title', $post->title, array('class' => 'text')) ?>
    </div>
  </div>
  <div class="news_form_param">
    <div class="news_form_header"><?php echo $post->getAttributeLabel('fullstory') ?></div>
    <?php echo ActiveHtml::textArea('fullstory', $post->fullstory, array('class' => 'text', 'onkeyup' => 'checkTextLength(4096, this, \'#news_warn\')')) ?>
    <div id="news_warn" class="news_warn"></div>
  </div>
  <div id="news_form_attach_wrap"></div>
  <?php $this->endWidget(); ?>
</div>