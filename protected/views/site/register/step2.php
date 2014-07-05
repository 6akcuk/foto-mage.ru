<?php Yii::app()->getClientScript()->registerScriptFile('/js/register.js'); ?>

<h1>Регистрация на сайте</h1>

<div id="stepcolumns" class="clearfix">
    <div class="left">
        <ul>
            <li>
                <?php echo ActiveHtml::link('Родной город', '/register/step1') ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Личные данные', '/register/step2', array('class' => 'selected')) ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Данные для входа', '/register/step3') ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Соглашение', '/register/step4') ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Завершение регистрации', '/register/step5') ?>
            </li>
        </ul>
    </div>
    <div class="right">
        <h3>Укажите личные данные</h3>
        <p>
            ФИО требуется для идентификация вашей личности при проведении платежей. <b>Поле Код приглашения не является обязательным.</b>
        </p>
        <?php /** @var $form ActiveForm */
        $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
            'id' => 'regform',
            'action' => $this->createUrl('/register'),
        )); ?>
        <input type="hidden" name="step" value="2" />
        <div class="clearfix">
          <?php echo $form->dropdown($model, 'gender', array('Мужской' => 'Male', 'Женский' => 'Female')) ?>
        </div>
        <div class="row">
            <?php echo $form->inputPlaceholder($model, 'lastname') ?>
        </div>
        <div class="row">
            <?php echo $form->inputPlaceholder($model, 'firstname') ?>
        </div>
        <div class="row">
            <?php echo $form->inputPlaceholder($model, 'middlename') ?>
        </div>
        <div class="row">
          <?php echo $form->inputPlaceholder($model, 'invite_code') ?>
        </div>
        <?php $this->endWidget(); ?>
        <div class="buttons clearfix">
            <div class="right">
                <a class="btn light_green" onclick="register.next()">
                    Далее
                    <span class="iconify_next_a"></span>
                </a>
            </div>
        </div>
    </div>
</div>