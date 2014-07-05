<?php
/** @var Organization $org
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$citiesList =
  (Yii::app()->user->hasAccessEntry(RBACFilter::getHierarchy() .'City'))
    ? array(Yii::app()->user->model->profile->city->name => Yii::app()->user->model->profile->city_id)
    : City::getDataArray();

$citiesJS = array();
$orgTypesJS = array();

foreach ($citiesList as $city_name => $city_id) {
  $citiesJS[] = "[". $city_id .",'". $city_name ."']";
}
$citiesJS = implode(",", $citiesJS);

foreach ($orgTypes as $type) {
  $orgTypesJS[] = "[". $type->type_id .",'". $type->type_name ."']";
}
$orgTypesJS = implode(",", $orgTypesJS);

$this->pageTitle = 'Редактирование организации';
?>
  <div class="org_content">
    <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
      'id' => 'org_form'
    )); ?>
    <div id="org_result"></div>
    <div id="org_error" class="error"></div>
    <div class="clear_fix">
      <div class="fl_l upload_wrap org_form_image_wrap">
        <?php echo ActiveHtml::hiddenField('photo', $org->photo) ?>
      </div>
      <div class="fl_l org_form_param">
        <div class="org_form_header"><?php echo $org->getAttributeLabel('org_type_id') ?></div>
        <div class="org_form_dropdown"><?php echo ActiveHtml::hiddenField('org_type_id', implode(",", $types)) ?></div>
      </div>
      <div class="fl_l org_form_param">
        <div class="org_form_header"><?php echo $org->getAttributeLabel('city_id') ?></div>
        <div class="org_form_dropdown"><?php echo ActiveHtml::hiddenField('city_id', $org->city_id) ?></div>
      </div>
    </div>
    <div class="org_form_param">
      <div class="org_form_header"><?php echo $org->getAttributeLabel('name') ?></div>
      <?php echo ActiveHtml::textField('name', $org->name, array('class' => 'text')) ?>
    </div>
    <div class="org_form_param">
      <div class="org_form_header"><?php echo $org->getAttributeLabel('address') ?></div>
      <?php echo ActiveHtml::textField('address', $org->address, array('class' => 'text')) ?>
    </div>
    <div class="org_form_param">
      <div class="org_form_header"><?php echo $org->getAttributeLabel('phone') ?></div>
      <?php echo ActiveHtml::textField('phone', $org->phone, array('class' => 'text')) ?>
    </div>
    <div class="org_form_param">
      <div class="org_form_header"><?php echo $org->getAttributeLabel('worktimes') ?></div>
      <?php echo ActiveHtml::textField('worktimes', $org->worktimes, array('class' => 'text')) ?>
    </div>
    <div class="org_form_param">
      <div class="org_form_header"><?php echo $org->getAttributeLabel('shortstory') ?></div>
      <?php echo ActiveHtml::textArea('shortstory', $org->shortstory, array('class' => 'text', 'onkeyup' => 'checkTextLength(256, this, \'#org_warn\')')) ?>
      <div id="org_warn" class="org_warn"></div>
    </div>
    <?php $this->endWidget(); ?>
  </div>
<?php
$this->pageJS = <<<HTML
Org.initOrgForm({
  uploadAction: 'http://cs1.e-bash.me/upload.php',
  cities: [$citiesJS],
  orgTypes: [$orgTypesJS]
});
HTML;
