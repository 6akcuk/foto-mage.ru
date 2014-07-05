<div class="box_header">
  Изменение номера мобильного телефона
  <a onclick="curBox().hide()" class="box_close right">Закрыть</a>
</div>
<div class="box_cont" style="text-align: center">
  Использование личного <b>номера телефона</b> позволит защитить Вашу страницу.

  <?php
  /** @var $changephoneform ActiveForm */
  $changephoneform = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'changephoneform',
    'action' => $this->createUrl('/settings'),
  )); ?>
  <input type="hidden" name="act" value="changephone" />
  <input type="hidden" name="eid" value="0" />
  <div class="row" style="margin-top: 20px">
    <h6>Мобильный телефон</h6>
    <?php echo $changephoneform->textField($changephonemdl, 'phone', array('style' => 'width: 120px', 'value' => '+7')) ?>
  </div>
  <div id="change_phone_code" class="row" style="display: none">
    <h6>Код подтверждения</h6>
    <?php echo $changephoneform->textField($changephonemdl, 'code', array('style' => 'width: 120px')) ?>
  </div>
  <a id="change_phone_button" class="button" onclick="Profile.getPhoneCode()" style="margin-top: 7px; width: 120px">Получить код</a>
  <?php $this->endWidget(); ?>
</div>