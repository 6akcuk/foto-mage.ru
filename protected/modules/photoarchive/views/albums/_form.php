<div class="album_form_content">
  <div id="album_result"></div>
  <div id="album_error" class="error"></div>
  <div class="album_form_param">
    <div class="album_form_header"><?php echo $album->getAttributeLabel('name') ?></div>
    <?php echo ActiveHtml::textField('name', $album->name, array('class' => 'text text_big')) ?>
  </div>
</div>