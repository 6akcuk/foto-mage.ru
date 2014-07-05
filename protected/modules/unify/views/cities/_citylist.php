<?php /** @var $city City */ ?>
<?php $page = ($offset + Yii::app()->controller->module->citiesPerPage) / Yii::app()->controller->module->citiesPerPage ?>
<?php $added = false; ?>
<?php foreach ($cities as $city): ?>
<tr<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?>>
  <td><?php echo $city->id ?></td>
  <td><?php echo $city->name ?></td>
  <td><?php echo Yii::t('app', $city->timezone) ?></td>
  <td>
    <a onclick="City.edit('<?php echo $city->id ?>')">Редактировать</a> |
    <a onclick="City.delete('<?php echo $city->id ?>')">Удалить</a>
  </td>
</tr>
<?php endforeach; ?>