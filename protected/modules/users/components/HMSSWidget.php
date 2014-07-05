<?php

class HMSSWidget extends CWidget {
    public $menu;

    public function run() {
        ActiveHtml::publishAssets();
        $this->render('hmsswidget', array('menu' => $this->menu));
    }
}