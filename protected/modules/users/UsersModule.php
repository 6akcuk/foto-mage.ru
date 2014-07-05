<?php

class UsersModule extends CWebModule
{
  public $usersPerPage = 50;
  public $rolesPerPage = 20;
  public $operationsPerPage = 20;
  public $onlineInterval = 10;
  public $friendsPerPage = 10;
  public $reputationPerPage = 10;
  public $wallPostsPerPage = 10;
  public $feedbacksPerPage = 10;

  public $inviteReputationBonus = 1;

	public function init()
	{
		// this method is called when the module is being created
		// you may place code here to customize the module or the application

		// import the module-level models and components
		$this->setImport(array(
			'users.models.*',
			'users.components.*',
            'users.components.views.*',
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
