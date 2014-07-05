<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 25.09.12
 * Time: 9:47
 * To change this template use File | Settings | File Templates.
 */

class ActiveForm extends CActiveForm {
    public function inputPlaceholder($model, $attribute, $htmlOptions = array()) {
        return ActiveHtml::activeInputPlaceholder($model, $attribute, $htmlOptions);
    }

    public function emailPlaceholder($model, $attribute, $htmlOptions = array()) {
        return ActiveHtml::activeEmailPlaceholder($model, $attribute, $htmlOptions);
    }

    public function passwordPlaceholder($model, $attribute, $htmlOptions = array()) {
        return ActiveHtml::activePasswordPlaceholder($model, $attribute, $htmlOptions);
    }

    public function dropdown($model, $attribute, $data, $htmlOptions = array()) {
        return ActiveHtml::activeDropdown($model, $attribute, $data, $htmlOptions);
    }

    public function inputCalendar($model, $attribute, $htmlOptions = array()) {
        return ActiveHtml::activeInputCalendar($model, $attribute, $htmlOptions);
    }

    public function upload($model, $attribute, $label ='', $htmlOptions = array()) {
        return ActiveHtml::activeUpload($model, $attribute, $label, $htmlOptions);
    }

    public function smartTextarea($model, $attribute, $htmlOptions = array()) {
        return ActiveHtml::activeSmartTextarea($model, $attribute, $htmlOptions);
    }
}