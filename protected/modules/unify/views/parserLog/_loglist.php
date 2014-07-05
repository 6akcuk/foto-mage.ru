<?php /** @var $log ParserLog */ ?>
<?php $page = ($offset + Yii::app()->controller->module->parserLogsPerPage) / Yii::app()->controller->module->parserLogsPerPage ?>
<?php $added = false; ?>
<?php foreach ($logs as $row_idx => $log): ?>
  <tr<?php if ($row_idx % 2) echo ' class="even"' ?>>
    <td><?php echo $log->log_id ?></td>
    <td><?php echo ActiveHtml::date($log->start_dt) ?></td>
    <td><?php echo ActiveHtml::date($log->end_dt) ?></td>
    <td>
      <?php echo $log->message ?>
    </td>
  </tr>
<?php endforeach; ?>