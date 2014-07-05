<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 26.09.12
 * Time: 16:18
 * To change this template use File | Settings | File Templates.
 */
/* @var $form ActiveForm */
Yii::app()->getClientScript()->registerScriptFile('/js/user.js');

$form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
    'id' => 'login-form',
    'action' => '/login',
    'enableClientValidation' => false,
    'htmlOptions' => array('autocomplete' => 'on'),
)); ?>
<div class="clear_fix">
  <div class="fl_l label"><?php echo $form->label($model, 'email') ?></div>
  <div class="fl_l labeled"><?php echo $form->emailField($model, 'email', array('class' => 'text', 'autocomplete' => 'on')); ?></div>
</div>
<?php echo $form->error($model, 'email') ?>
<div class="clear_fix">
  <div class="fl_l label"><?php echo $form->label($model, 'password') ?></div>
  <div class="fl_l labeled"><?php echo $form->passwordField($model, 'password', array('class' => 'text')); ?></div>
</div>
<?php echo $form->error($model, 'password') ?>
<div class="button_blue button_big button_wide">
  <button onclick="$('#login-form').submit()">Войти</button>
</div>
<a class="reminder" href="/forgot">Забыли пароль?</a>
<?php $this->endWidget(); ?>