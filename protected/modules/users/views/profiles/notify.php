<?php

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerScriptFile('/js/profile.js');

$this->pageTitle = Yii::app()->name .' - Оповещения';

/** @var ActiveForm $emailnotifyform */
?>

<div class="tabs">
  <?php echo ActiveHtml::link('Общие', '/settings') ?>
  <?php echo ActiveHtml::link('Оповещения', '/notify', array('class' => 'selected')) ?>
</div>

<div class="profile-settings">
  <?php if ($report): ?>
    <div class="settings">
      <div class="op_report"><?php echo $report ?></div>
    </div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="settings">
      <div class="op_error"><?php echo $error ?></div>
    </div>
  <?php endif; ?>
  <div class="settings">
    <h2>Оповещения по электронной почте</h2>
    <?php
    /** @var $changepwdform ActiveForm */
    $emailnotifyform = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
      'id' => 'emailnotifyform',
      'action' => $this->createUrl('/notify'),
    )); ?>
    <input type="hidden" name="act" value="emailnotify" />
    <div class="row">
      <?php echo $emailnotifyform->label($notify, 'notify_im') ?>
      <?php echo $emailnotifyform->dropdownList($notify, 'notify_im', array('Не получать', 'Получать')) ?>
    </div>
    <div class="row">
      <?php echo $emailnotifyform->label($notify, 'notify_purchases') ?>
      <?php echo $emailnotifyform->dropdownList($notify, 'notify_purchases', array('Не получать', 'Получать')) ?>
    </div>
    <div class="row">
      <?php echo $emailnotifyform->label($notify, 'notify_comments') ?>
      <?php echo $emailnotifyform->dropdownList($notify, 'notify_comments', array('Не получать', 'Получать')) ?>
    </div>
    <?php if (Yii::app()->user->checkAccess('purchases.purchases.create')): ?>
    <div class="row">
      <?php echo $emailnotifyform->label($notify, 'notify_orders') ?>
      <?php echo $emailnotifyform->dropdownList($notify, 'notify_orders', array('Не получать', 'Получать')) ?>
    </div>
    <div class="row">
      <?php echo $emailnotifyform->label($notify, 'notify_payments') ?>
      <?php echo $emailnotifyform->dropdownList($notify, 'notify_payments', array('Не получать', 'Получать')) ?>
    </div>
    <?php endif; ?>
    <div class="row">
      <a class="button" onclick="Profile.emailNotify()">Сохранить</a>
    </div>
    <?php $this->endWidget(); ?>
  </div>
</div>