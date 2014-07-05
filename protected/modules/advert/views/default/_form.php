<div class="advert_category_content">
  <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'post_form'
  )); ?>
  <div id="advert_post_result"></div>
  <div id="advert_post_error" class="error"></div>
  <div class="advert_post_header"><?php echo $post->getAttributeLabel('category_id') ?></div>
  <div class="advert_post_param"><?php echo ActiveHtml::hiddenField('category_id', $post->category_id, array('class' => 'text')) ?></div>
  <div class="advert_post_header"><?php echo $post->getAttributeLabel('city_id') ?></div>
  <div class="advert_post_param"><?php echo ActiveHtml::hiddenField('city_id', $post->city_id, array('class' => 'text')) ?></div>
  <div id="advert_params_label" class="advert_post_header"<?php if (!$post->params): ?> style="display: none"<?php endif; ?>>Параметры</div>
  <div id="advert_params" class="advert_post_param"<?php if (!$post->params): ?> style="display: none"<?php endif; ?>>
  <?php foreach ($post->params as $param): ?>
    <input type="hidden" id="param_<?php echo $param->param_id ?>" name="param[<?php echo $param->param_id ?>]" value="<?php echo $param->param_value ?>" />
  <?php endforeach; ?>
  </div>
  <div id="advert_post_title" class="advert_post_header"><?php echo $post->getAttributeLabel('title') ?></div>
  <div id="advert_post_title_inp" class="advert_post_param"><?php echo ActiveHtml::textField('title', $post->title, array('class' => 'text')) ?></div>
  <div class="advert_post_header"><?php echo $post->getAttributeLabel('fullstory') ?></div>
  <div class="advert_post_param"><?php echo ActiveHtml::textArea('fullstory', $post->fullstory, array('class' => 'text')) ?></div>
  <div id="advert_post_attach_wrap"></div>
  <div id="advert_post_price" class="advert_post_header"><?php echo $post->getAttributeLabel('price') ?></div>
  <div id="advert_post_price_inp" class="advert_post_param"><?php echo ActiveHtml::textField('price', $post->price, array('class' => 'text')) ?></div>
  <div class="advert_post_param"><?php echo ActiveHtml::hiddenField('fixed', $post->fixed) ?></div>
  <?php $this->endWidget(); ?>
</div>