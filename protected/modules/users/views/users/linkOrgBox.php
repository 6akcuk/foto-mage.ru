<?php
/**
 * @var User $user
 * @var Organization $org
 * @var UserOrgLink $link
 */

$orgsJS = array();
foreach ($orgs as $org) {
  $orgsJS[] = array($org->org_id, $org->name, false, false, null, $org->city->name); // "[". $org->org_id .",'". $org->name ."']";
}
$orgsJS = json_encode($orgsJS);

$this->pageTitle = 'Закрепить организации за пользователем';
?>
<div id="link_org_error" class="error"></div>
<div class="orgs_container"><?php echo ActiveHtml::hiddenField('org_id') ?></div>
<div class="link_org_user_wrap">
<?php foreach ($links as $link): ?>
  <?php $this->renderPartial('_linkorg', array('link' => $link)) ?>
<?php endforeach; ?>
</div>
<?php
$this->pageJS = <<<HTML
Users.initLinkOrg({
  id: $user->id,
  orgs: $orgsJS
});
HTML;
