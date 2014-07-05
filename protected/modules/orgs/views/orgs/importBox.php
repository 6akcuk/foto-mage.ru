<?php
/** @var Organization $org
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$citiesJS = array();
foreach ($cities as $city) {
  $citiesJS[] = "[$city->id,'$city->name']";
}
$citiesJS = implode(",", $citiesJS);

$this->pageTitle = 'Импортировать данные';
?>
  <div class="org_content">
    <?php $form = $this->beginWidget('ext.ActiveHtml.ActiveForm', array(
      'id' => 'org_form',
      'htmlOptions'=>array('enctype'=>'multipart/form-data'),
    )); ?>
    <div id="org_result"></div>
    <div id="org_error" class="error"></div>
    <div class="org_form_param">
      <div class="org_form_header">Город</div>
      <div class="org_form_dropdown"><?php echo ActiveHtml::hiddenField('city_id') ?></div>
    </div>
    <div class="org_form_param">
      <div class="org_form_header">Источник</div>
      <div class="org_form_dropdown"><?php echo ActiveHtml::hiddenField('source') ?></div>
    </div>
    <div class="org_form_param">
      <div class="org_form_header">Файл</div>
      <div class="org_form_dropdown"><?php echo ActiveHtml::fileField('filedata') ?></div>
    </div>
    <?php $this->endWidget(); ?>
  </div>
<?php
$this->pageJS = <<<HTML
Org.initImportForm({
  cities: [$citiesJS]
});
HTML;
