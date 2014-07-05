<?php /** @var $reputation ProfileReputation */ ?>
<?php $page = ($offset + Yii::app()->controller->module->reputationPerPage) / Yii::app()->controller->module->reputationPerPage ?>
<?php $added = false; ?>
<?php foreach ($data as $reputation): ?>
<div<?php if(!$added) { echo ' rel="page-'. $page .'"'; $added = true; } ?> id="rep<?php echo $reputation->rep_id ?>" class="people clearfix">
    <div class="left photo">
        <?php echo ($reputation->author->profile->photo) ? ActiveHtml::showUploadImage($reputation->author->profile->photo) : '<img src="/images/camera_a.gif" />' ?>
    </div>
    <div class="left info rep-long">
        <div class="labeled name">
            <?php echo ActiveHtml::link(
            (Yii::app()->user->checkAccess('global.fullnameView'))
                ? $reputation->author->profile->firstname .' '. $reputation->author->profile->lastname .' ('. $reputation->author->login .')'
                : $reputation->author->login, '/id'. $reputation->author_id) ?>
        </div>
        <?php if ($reputation->author->isOnline()): ?>
        <div class="online">Online</div>
        <?php endif; ?>
        <div class="comment">
        <?php echo nl2br($reputation->comment) ?>
        </div>
    </div>
    <div class="right rep-info profile-<?php echo ($reputation->value < 0) ? 'negative' : 'positive' ?>-rep">
        <?php echo (($reputation->value > 0) ? '+' : '') . $reputation->value ?>
    </div>
    <div class="reputation_del_wrap">
        <div class="reputation_del" rel="tooltip" title="Удалить репутацию" onclick="return Profile.deleteReputation(<?php echo $reputation->rep_id ?>)"></div>
    </div>
</div>
<?php endforeach; ?>
