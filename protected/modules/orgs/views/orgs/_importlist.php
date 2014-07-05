<?php /** @var $imp ImportData */ ?>
<?php foreach ($import as $row_idx => $imp): ?>
  <tr<?php if ($row_idx % 2) echo ' class="even"' ?>>
    <td><?php echo $imp->filename ?></td>
    <td>
    <?php
      $data = json_decode($imp->data, true);
      $city = City::model()->findByPk($data['city_id']);
      echo $city->name;
    ?>
    </td>
    <td>
      <b id="imp<?php echo $imp->id ?>_completed"><?php echo $imp->completed ?></b> / <b><?php echo $imp->total ?></b>
      <br />
      данных
    </td>
    <td id="imp<?php echo $imp->id ?>_status">
      <?php echo Yii::t('app', $imp->status) ?>
    </td>
    <td class="org_actions">
    <?php if ($imp->status != ImportData::STATUS_COMPLETED): ?>
      <div class="button_blue">
        <button onclick="Org.importRun(this, <?php echo $imp->id ?>)">Импортировать</button>
      </div>
    <?php else: ?>
      -
    <?php endif; ?>
    </td>
  </tr>
<?php endforeach; ?>