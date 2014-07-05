<?php /** @var $feedback Feedback */ ?>
<?php $page = ($offset + Yii::app()->controller->module->feedbacksPerPage) / Yii::app()->controller->module->feedbacksPerPage ?>
<?php $added = false; ?>
<?php foreach ($feedbacks as $feedback): ?>
  <tr<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?>>
    <td><?php echo $feedback->feedback_id ?></td>
    <td><?php echo ($feedback->author) ? $feedback->author->email : "Анонимно" ?></td>
    <td><?php echo ($feedback->author) ? $feedback->author->getDisplayName() : "Анонимно" ?></td>
    <td><?php echo ($feedback->author) ? $feedback->author->profile->city->name : "Анонимно" ?></td>
    <td><?php echo ActiveHtml::date($feedback->add_date) ?></td>
    <td>
      <?php echo nl2br($feedback->message) ?>
    </td>
    <td>
      <a onclick="Feedback.edit('<?php echo $feedback->feedback_id ?>')">Редактировать</a> |
      <a onclick="Feedback.delete('<?php echo $feedback->feedback_id ?>')">Удалить</a>
    </td>
  </tr>
<?php endforeach; ?>