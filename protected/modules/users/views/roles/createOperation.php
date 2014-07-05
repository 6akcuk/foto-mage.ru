<?php
$this->pageTitle=Yii::app()->name . ' - Добавить новую операцию';

Yii::app()->getClientScript()->registerScriptFile('/js/operation.js');
?>

<div id="cols" class="clearfix">
    <div class="col_large">
        <div id="tabs">
            <?php echo ActiveHtml::link('Пользователи', $this->createUrl('users/index')) ?>
            <?php echo ActiveHtml::link('Роли', $this->createUrl('roles/index'), array('class' => 'selected')) ?>
        </div>
        <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
        'id' => 'addoperation-form',
        'action' => $this->createUrl('roles/createOperation'),
        'enableClientValidation' => true,
        )); ?>
        <h2>Добавить новую операцию</h2>
        <div class="row">
            <?php echo $form->inputPlaceholder($model, 'name'); ?>
        </div>
        <div class="row">
            <?php echo $form->inputPlaceholder($model, 'description'); ?>
        </div>
        <div class="row">
            <?php echo $form->inputPlaceholder($model, 'bizrule'); ?>
        </div>
        <div class="row">
            <?php echo ActiveHtml::submitButton('Создать', array('class' => 'btn light_blue', 'onclick' => 'return FormMgr.submit("#addoperation-form")')); ?>
        </div>
        <?php $this->endWidget(); ?>
    </div>
    <div class="col_small">
        <?php $this->renderPartial('_menu', array('selected' => 'Операции')) ?>
    </div>
</div>