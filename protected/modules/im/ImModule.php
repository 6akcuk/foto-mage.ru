<?php

class ImModule extends CWebModule
{
    public $dialogsPerPage = 10;
    public $messagesPerPage = 20;
    public $addRowInterval = 240; // время в сек. между сообщениями, когда считать их от одного отправителя

	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'im.models.*',
			'im.components.*',
		));
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}
}
