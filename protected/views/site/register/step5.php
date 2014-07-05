<?php Yii::app()->getClientScript()->registerScriptFile('/js/register.js'); ?>

<h1>Регистрация на сайте</h1>

<div id="stepcolumns" class="clearfix">
    <div class="left">
        <ul>
            <li>
                <?php echo ActiveHtml::link('Родной город', '/register/step1') ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Личные данные', '/register/step2') ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Данные для входа', '/register/step3') ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Соглашение', '/register/step4') ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Завершение регистрации', '/register/step5', array('class' => 'selected')) ?>
            </li>
        </ul>
    </div>
    <div class="right">
        <h3>Завершение регистрации</h3>
        <p>
            На указанный вами мобильный телефон, было отправлено SMS-сообщение с кодом подтверждения
        </p>
        <?php /** @var $form ActiveForm */
        $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
          'id' => 'regform',
          'action' => $this->createUrl('/register'),
          'htmlOptions' => array(
            'onsubmit' => 'register.next(); return false',
          )
        )); ?>
        <input type="hidden" name="step" value="5" />
        <div class="row">
            <?php echo $form->inputPlaceholder($model, 'confirm') ?>
            <a id="sendCodeLink" onclick="register.sendCode(true)" title="Повторно отправить код подтверждения" class="tt">Повторно отправить код</a>
        </div>
        <?php $this->endWidget(); ?>
        <div class="buttons clearfix">
            <div class="right">
                <a class="btn light_green" onclick="register.next()">
                    Завершить
                </a>
            </div>
        </div>
    </div>
</div>