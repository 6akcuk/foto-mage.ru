<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 17.10.12
 * Time: 23:24
 * To change this template use File | Settings | File Templates.
 */

class RoleForm extends CFormModel {
    public $name;
    public $description;
    public $bizrule;

    public function rules() {
        return array(
            array('name, description', 'required'),
        );
    }

    public function attributeLabels() {
        return array(
            'name' => 'Имя роли',
            'description' => 'Описание',
            'bizrule' => 'Бизнес-правило',
        );
    }
}