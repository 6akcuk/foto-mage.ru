<?php

Yii::app()->getClientScript()->registerCssFile('/css/discuss.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discuss.js');

$this->pageTitle = Yii::app()->name .' - Новый форум';
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Обсуждения', '/discuss?act=manage') ?>
  <?php echo ActiveHtml::link('Новый форум', '/discuss?act=create', array('class' => 'selected')) ?>
</div>
<div class="bnt_wrap">
  <div class="bnt_header">Название форума</div>
  <input type="text" id="bnt_title" />
  <div class="bnt_header">Описание</div>
  <textarea id="bnt_description"></textarea>
  <div class="bnt_header">Родительский форум</div>
  <?php echo ActiveHtml::dropDownList('bnt_parent', '', DiscussForum::getRootForums()) ?>
  <div class="bnt_header">Иконка</div>
  <?php echo ActiveHtml::upload('icon', '', 'Выберите изображение', array('data-image' => 'c')) ?>
  <table class="bnt_two_columns" style="margin-top: 20px">
    <tr>
      <td width="50%">
        <div class="bnt_header">Ограничение доступа по городу</div>
        <?php echo ActiveHtml::dropDownList('bnt_city', '', City::getListArray()) ?>
      </td>
      <td>
        <div class="bnt_header">Ограничение доступа по группе пользователя</div>
        <?php echo ActiveHtml::dropDownList('bnt_rights', '', DiscussForum::$rightsArray) ?>
      </td>
    </tr>
  </table>

  <div class="bnt_buttons clearfix">
    <a class="button left" onclick="Discuss.createForum()">Создать форум</a>
    <div id="bnt_progress" class="upload left"><img src="/images/upload.gif" /></div>
  </div>
</div>