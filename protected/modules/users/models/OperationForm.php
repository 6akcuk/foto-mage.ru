<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 17.10.12
 * Time: 23:24
 * To change this template use File | Settings | File Templates.
 */

class OperationForm extends CFormModel {
    public $name;
    public $description;
    public $bizrule;

    public function rules() {
        return array(
            array('name, description', 'required'),
            array('bizrule', 'safe'),
        );
    }

    public function attributeLabels() {
        return array(
            'name' => 'Код операции',
            'description' => 'Описание',
            'bizrule' => 'Бизнес-правило',
        );
    }
}