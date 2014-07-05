<?php

Yii::app()->getClientScript()->registerCssFile('/css/discuss.css');
Yii::app()->getClientScript()->registerScriptFile('/js/discuss.js');

$this->pageTitle = Yii::app()->name .' - Редактирование форума';
?>
<div class="tabs">
  <?php echo ActiveHtml::link('Обсуждения', '/discuss?act=manage') ?>
  <?php echo ActiveHtml::link('Редактирование форума', '/discuss?act=edit&id='. $id, array('class' => 'selected')) ?>
</div>
<div class="bnt_wrap">
  <div class="bnt_header">Название форума</div>
  <input type="text" id="bnt_title" value="<?php echo $forum->title ?>" />
  <div class="bnt_header">Описание</div>
  <textarea id="bnt_description"><?php echo $forum->description ?></textarea>
  <div class="bnt_header">Родительский форум</div>
  <?php echo ActiveHtml::dropDownList('bnt_parent', $forum->parent_id, DiscussForum::getRootForums()) ?>
  <div class="bnt_header">Иконка</div>
  <?php echo ActiveHtml::upload('icon', $forum->icon, 'Выберите изображение', array('data-image' => 'c')) ?>
  <table class="bnt_two_columns" style="margin-top: 20px">
    <tr>
      <td width="50%">
        <div class="bnt_header">Ограничение доступа по городу</div>
        <?php echo ActiveHtml::dropDownList('bnt_city', $forum->access_city, City::getListArray()) ?>
      </td>
      <td>
        <div class="bnt_header">Ограничение доступа по группе пользователя</div>
        <?php echo ActiveHtml::dropDownList('bnt_rights', $forum->access_rights, DiscussForum::$rightsArray) ?>
      </td>
    </tr>
  </table>

  <div class="bnt_buttons clearfix">
    <a class="button left" onclick="Discuss.doEditForum(<?php echo $id ?>)">Сохранить</a>
    <div id="bnt_progress" class="upload left"><img src="/images/upload.gif" /></div>
  </div>
</div>