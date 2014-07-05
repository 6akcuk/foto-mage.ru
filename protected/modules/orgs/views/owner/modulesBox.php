<?php
/** @var Organization $org
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/orgs.css');
Yii::app()->getClientScript()->registerScriptFile('/js/orgs.js');

$this->pageTitle = 'Управление модулями';
?>
<div class="org_form_param">
  <div id="org_result"></div>
  <div id="org_error" class="error"></div>
  <?php echo ActiveHtml::hiddenField('enable_delivery', $org->modules && $org->modules->enable_delivery) ?>
  <?php echo ActiveHtml::hiddenField('enable_discount', $org->modules && $org->modules->enable_discount) ?>
  <?php echo ActiveHtml::hiddenField('enable_market', $org->modules && $org->modules->enable_market) ?>
</div>
<?php
$this->pageJS = <<<HTML
cur.uiOrgModuleDelivery = new Checkbox('enable_delivery', {label: 'Включить раздел «Доставка» для данной организации'});
cur.uiOrgModuleDiscount = new Checkbox('enable_discount', {label: 'Включить раздел «Дисконтная система» для данной организации'});
cur.uiOrgModuleMarket = new Checkbox('enable_market', {label: 'Включить раздел «Товары и услуги (Магазины)» для данной организации'});
HTML;
