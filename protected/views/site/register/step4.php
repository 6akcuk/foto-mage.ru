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
                <?php echo ActiveHtml::link('Соглашение', '/register/step4', array('class' => 'selected')) ?>
            </li>
            <li>
                <?php echo ActiveHtml::link('Завершение регистрации', '/register/step5') ?>
            </li>
        </ul>
    </div>
    <div class="right">
        <h3>Соглашение</h3>
        <p>
            Настоящим соглашением подтверждается, что Абонент, персональные данные которого являются предметом
            соглашения, выражает свое согласие на получение сообщений информационного и рекламного содержания.
        </p>
        <p>
            Для отзыва согласия необходимо в настройках аккаунта отключить отправку смс сообщений или же по запросу в
            техническую поддержку.
        </p>
        <?php /** @var $form ActiveForm */
        $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
          'id' => 'regform',
          'action' => $this->createUrl('/register'),
          'htmlOptions' => array(
            'onsubmit' => 'register.next(); return false',
          )
        )); ?>
        <input type="hidden" name="step" value="4" />
        <div class="row">
            +7 <?php echo $form->inputPlaceholder($model, 'phone') ?>
            <a id="sendCodeLink" onclick="register.sendCode(true)" title="Повторно отправить код подтверждения" class="tt iconify_refresh_a" style="display:none"></a>
        </div>
        <div class="row">
            <input type="checkbox" name="RegisterForm[agreement]" value="1"<?php if($model->agreement) echo " checked" ?>/>
            <?php echo $form->label($model, 'agreement') ?>
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