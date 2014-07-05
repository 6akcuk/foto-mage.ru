<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 07.01.13
 * Time: 11:26
 * To change this template use File | Settings | File Templates.
 */

class DevelopFilter extends CFilter {
    protected function preFilter($filterChain) {
        if (Yii::app()->user->model->role->itemname != "Администратор") {
            throw new CHttpException(403, 'В разработке');
            return false;
        }
        return true;
    }
}