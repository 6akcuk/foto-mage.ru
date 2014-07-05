<?php
/** @var $people User */

Yii::app()->getClientScript()->registerCssFile('/css/profile.css');

Yii::app()->getClientScript()->registerCssFile('/css/pagination.css');
Yii::app()->getClientScript()->registerScriptFile('/js/pagination.js');

Yii::app()->getClientScript()->registerScriptFile('/js/profile.js');

$this->pageTitle = Yii::app()->name .' - Репутация';
$delta = Yii::app()->controller->module->reputationPerPage;
?>
<div class="right clearfix">
<?php $this->widget('Paginator', array(
    'url' => '/reputation'. $user->id,
    'offset' => $offset,
    'offsets' => $offsets,
    'delta' => $delta,
)); ?>
</div>
<div rel="pagination">
    <?php echo $this->renderPartial('_reputation', array('data' => $data, 'offset' => $offset)) ?>
</div>
<? if ($offset + $delta < $offsets && $offsets > $delta): ?><a id="pg_more" class="pg_more" onclick="Paginator.showMore()">Еще репутация</a><? endif; ?>