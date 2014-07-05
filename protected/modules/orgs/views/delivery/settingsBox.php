<?php
/** @var DeliverySettings $settings
 * @var ActiveForm $form
 */

Yii::app()->getClientScript()->registerCssFile('/css/delivery.css');
Yii::app()->getClientScript()->registerScriptFile('/js/delivery.js');

Yii::app()->getClientScript()->registerCssFile('/css/upload.css');
Yii::app()->getClientScript()->registerScriptFile('/js/upload.js');

$this->pageTitle = 'Настройки доставки';
?>
  <div class="delivery_form_content">
    <div id="delivery_result"></div>
    <div id="delivery_error" class="error"></div>
    <div class="delivery_form_header"><?php echo $settings->getAttributeLabel('fullstory') ?></div>
    <div class="delivery_form_param"><?php echo ActiveHtml::textArea('fullstory', $settings->fullstory, array('class' => 'text')) ?></div>
    <div class="delivery_form_header"><?php echo $settings->getAttributeLabel('logo') ?></div>
    <div class="delivery_form_param"><?php echo ActiveHtml::hiddenField('logo', $settings->logo) ?></div>
    <div class="delivery_form_header"><?php echo $settings->getAttributeLabel('sms_phone') ?></div>
    <div class="delivery_form_param"><?php echo ActiveHtml::textField('sms_phone', $settings->sms_phone, array('class' => 'text')) ?></div>
    <div class="delivery_form_param"><?php echo ActiveHtml::hiddenField('disable_cart', $settings->disable_cart) ?></div>
  </div>
<?php
$this->pageJS = <<<HTML
cur.uploadAction = 'http://cs1.e-bash.me/upload.php';

Delivery.initSettingsForm();
HTML;
