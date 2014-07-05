<?php

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');
Yii::app()->getClientScript()->registerScriptFile('/js/profile.js');

$this->pageTitle = Yii::app()->name .' - Общие настройки';
?>

<div class="tabs">
  <?php echo ActiveHtml::link('Общие', '/settings', array('class' => 'selected')) ?>
  <?php //echo ActiveHtml::link('Оповещения', '/notify') ?>
</div>

<div class="profile_settings">
  <div class="settings_section">
    <div id="profile_settings_error" class="error"<?php if ($error): ?> style="display: block"<?php endif; ?>><?php if ($error) echo $error ?></div>
    <div id="profile_settings_result"<?php if ($report): ?> style="display: block"<?php endif; ?>><?php if ($report): ?><div class="msg"><?php echo $report ?></div><?php endif; ?></div>
  </div>

  <?php if (!Yii::app()->user->model->sn_name): ?>
  <div class="settings_section">
    <h2>Изменить пароль</h2>
    <div class="profile_settings_row clear_fix">
      <div class="settings_label fl_l ta_r">Старый пароль:</div>
      <div class="fl_l"><?php echo ActiveHtml::passwordField('old_password', '', array('class' => 'text')) ?></div>
    </div>
    <div class="profile_settings_row clear_fix">
      <div class="settings_label fl_l ta_r">Новый пароль:</div>
      <div class="fl_l"><?php echo ActiveHtml::passwordField('new_password', '', array('class' => 'text')) ?></div>
    </div>
    <div class="profile_settings_row clear_fix">
      <div class="settings_label fl_l ta_r">Повторите пароль:</div>
      <div class="fl_l"><?php echo ActiveHtml::passwordField('rpt_password', '', array('class' => 'text')) ?></div>
    </div>
    <div class="profile_settings_row clear_fix">
      <div class="settings_label fl_l ta_r"></div>
      <div class="fl_l">
        <div class="button_blue">
          <button onclick="Profile.changePassword(this)">Изменить пароль</button>
        </div>
      </div>
    </div>
  </div>

  <div class="settings_section">
    <h2>Адрес Вашей электронной почты</h2>
    <div class="profile_settings_row clear_fix">
      <div class="settings_label fl_l ta_r">Текущий адрес:</div>
      <div class="fl_l"><?php echo preg_replace("/(\w{1}).*([@]{1}.*)/ui", "$1***$2", Yii::app()->user->model->email) ?></div>
    </div>
    <div class="profile_settings_row clear_fix">
      <div class="settings_label fl_l ta_r">Новый адрес:</div>
      <div class="fl_l"><?php echo ActiveHtml::textField('new_mail', '', array('class' => 'text')) ?></div>
    </div>
    <div class="profile_settings_row clear_fix">
      <div class="settings_label fl_l ta_r"></div>
      <div class="fl_l">
        <div class="button_blue">
          <button onclick="Profile.saveEmail(this)">Сохранить адрес</button>
        </div>
      </div>
    </div>
  </div>
  <?php else: ?>
    <div id="no_results">
      Здесь будут отображаться настройки Вашего профиля.
    </div>
  <?php endif; ?>
</div>