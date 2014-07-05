<?php
$this->pageTitle=Yii::app()->name . ' - Связь роли с операциями';

Yii::app()->getClientScript()->registerScriptFile('/js/link-role-operation.js');

$rolesJs = array();
$operationsJs = array();
$childsJs = array();

foreach ($roles as $role) {
    $rolesJs[] = "['". addslashes($role->name) ."','". addslashes($role->description) ."']";
}
foreach ($operations as $op) {
    $operationsJs[] = "['". addslashes($op->name) ."','". addslashes($op->description) ."']";
}
foreach ($roleChilds as $role => $childs) {
    $c = array();
    foreach ($childs as $child) {
        $c[] = "'". $child->child ."'";
    }
    $childsJs[] = "'". $role ."': [". implode(",", $c) ."]";
}
?>

<div id="cols" class="clearfix">
    <div class="col_large">
        <div id="tabs">
            <?php echo ActiveHtml::link('Пользователи', $this->createUrl('users/index')) ?>
            <?php echo ActiveHtml::link('Роли', $this->createUrl('roles/index'), array('class' => 'selected')) ?>
        </div>
        <div id="desktop">
            <ul class="blocks">
                <?php foreach ($roles as $role): ?>
                <li>
                    <a onclick="LinkRoleOperation.edit('<?php echo $role->name ?>')">
                        <?php echo $role->name ?>
                        <span class="iconify">]</span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="col_small">
        <?php $this->renderPartial('_menu', array('selected' => 'Связь роли с операциями')) ?>
    </div>
</div>

<script type="text/javascript">
var rolesList = [<?php echo implode(',', $rolesJs) ?>], operationsList = [<?php echo implode(',', $operationsJs) ?>],
    roleChilds = {<?php echo implode(',', $childsJs) ?>};
</script>