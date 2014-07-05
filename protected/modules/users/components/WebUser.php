<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Sum
 * Date: 25.09.12
 * Time: 16:50
 * To change this template use File | Settings | File Templates.
 */

class WebUser extends CApplicationComponent implements IWebUser {
    public $allowAutoLogin = false;
    public $autoRenewCookie = false;
    public $duration = 2592000;

  /** @var $model User */
    public $model;

    protected $guestName = 'Гость';

    private $_keyPrefix;
    private $_access=array();

    public function init() {
        parent::init();
        Yii::app()->getSession()->open();
        if($this->getIsGuest() && $this->allowAutoLogin)
            $this->restoreFromCookie();
        if(!$this->getIsGuest()) {
          $this->model = User::model()->findByPk($this->getId());
          $this->updateLastVisit();
        }
    }

    public function getIsGuest() {
        return $this->getState('__id') === null;
    }

    public function getId() {
        return $this->getState('__id');
    }
    public function setId($id) {
        $this->setState('__id', $id);
    }

  public function setHash($hash) {
    $this->setState('__hash', $hash);
  }
  public function getHash() {
    return $this->getState('__hash');
  }

    /**
     * Returns the unique identifier for the user (e.g. username).
     * This is the unique identifier that is mainly used for display purpose.
     * @return string the user name. If the user is not logged in, this will be {@link guestName}.
     */
    public function getName()
    {
        if(($name=$this->getState('__name'))!==null)
            return $name;
        else
            return $this->guestName;
    }

    /**
     * Sets the unique identifier for the user (e.g. username).
     * @param string $value the user name.
     * @see getName
     */
    public function setName($value)
    {
        $this->setState('__name',$value);
    }

    public function loginRequired() {

    }

    public function login($identity) {
      $id = $identity->getId();
      $hash = $identity->getHash();
        if ($this->beforeLogin($id, false)) {
            $this->changeIdentity($id, $hash);

            if ($this->duration > 0) {
                if ($this->allowAutoLogin)
                    $this->saveToCookie();
            }

            $this->afterLogin(false);
        }
    }

    public function logout($destroySession = true) {
        if($this->beforeLogout()) {
            if($this->allowAutoLogin)
                $this->removeCookies();
            if($destroySession)
                Yii::app()->getSession()->destroy();
            else
                $this->clearStates();

            $this->afterLogout();
        }
    }

    public function updateLastVisit() {
      User::model()->updateByPk($this->getId(), array('lastvisit' => date("Y-m-d H:i:s")));
    }

    public function restoreFromCookie() {
        $app=Yii::app();
        $request = $app->getRequest();
        /** @var $cookies CCookieCollection */
        $cookies = $request->getCookies();

        if (isset($cookies['uid']) && isset($cookies['p'])) {
            /** @var $uid CHttpCookie */
            $uid = $cookies['uid'];
            /** @var $phash CHttpCookie */
            $phash = $cookies['p'];

            if ($this->beforeLogin($uid->value, true)) {
                $user = User::model()->findByPk($uid->value);

                if ($user && $user->hash == $phash->value) {
                    $this->changeIdentity($uid->value, $user->hash);

                    if ($this->autoRenewCookie) {
                        $uid->expire = time() + $this->duration;
                        $phash->expire = time() + $this->duration;

                        $cookies->add('uid', $uid);
                        $cookies->add('p', $phash);
                    }

                    $this->afterLogin(true);
                }
            }
        }
    }

    public function removeCookies() {
      $options = array('domain' => '.'. Yii::app()->params['domain']);
      Yii::app()->getRequest()->getCookies()->remove('uid', $options);
      Yii::app()->getRequest()->getCookies()->remove('p', $options);
    }

    public function saveToCookie() {
      $app=Yii::app();

      $uid = new CHttpCookie('uid', $this->getId());
      $uid->expire = time() + $this->duration;
      $uid->domain = '.'. Yii::app()->params['domain'];

      $phash = new CHttpCookie('p', $this->createCookieHash());
      $phash->expire = time() + $this->duration;
      $phash->domain = '.'. Yii::app()->params['domain'];

      $user = User::model()->findByPk($this->getId());
      $user->hash = $phash->value;
      $user->save(true, array('hash'));

      $this->model = $user;

      $app->getRequest()->getCookies()->add('uid', $uid);
      $app->getRequest()->getCookies()->add('p', $phash);
    }

    public function createCookieHash() {
        return md5(time() . $this->getId() . Yii::app()->getId());
    }

    public function changeIdentity($id, $hash) {
        Yii::app()->getSession()->regenerateID(true);
        $this->setId($id);
      $this->setHash($hash);
    }

    /**
     * @param $id
     * @param $fromCookie
     * @return bool
     */
    public function beforeLogin($id, $fromCookie) {
        return true;
    }

    /**
     * @param $fromCookie
     */
    public function afterLogin($fromCookie) {

    }

    public function beforeLogout() {
        return true;
    }

    public function afterLogout() {

    }

    public function getStateKeyPrefix()
    {
        if($this->_keyPrefix!==null)
            return $this->_keyPrefix;
        else
            return $this->_keyPrefix=md5('Yii.'.get_class($this).'.'.Yii::app()->getId());
    }

    /**
     * @param string $value a prefix for the name of the session variables storing user session data.
     */
    public function setStateKeyPrefix($value)
    {
        $this->_keyPrefix=$value;
    }

    /**
     * Returns the value of a variable that is stored in user session.
     *
     * This function is designed to be used by CWebUser descendant classes
     * who want to store additional user information in user session.
     * A variable, if stored in user session using {@link setState} can be
     * retrieved back using this function.
     *
     * @param string $key variable name
     * @param mixed $defaultValue default value
     * @return mixed the value of the variable. If it doesn't exist in the session,
     * the provided default value will be returned
     * @see setState
     */
    public function getState($key,$defaultValue=null)
    {
        $key=$this->getStateKeyPrefix().$key;
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
    }

    /**
     * Stores a variable in user session.
     *
     * This function is designed to be used by CWebUser descendant classes
     * who want to store additional user information in user session.
     * By storing a variable using this function, the variable may be retrieved
     * back later using {@link getState}. The variable will be persistent
     * across page requests during a user session.
     *
     * @param string $key variable name
     * @param mixed $value variable value
     * @param mixed $defaultValue default value. If $value===$defaultValue, the variable will be
     * removed from the session
     * @see getState
     */
    public function setState($key,$value,$defaultValue=null)
    {
        $key=$this->getStateKeyPrefix().$key;
        if($value===$defaultValue)
            unset($_SESSION[$key]);
        else
            $_SESSION[$key]=$value;
    }

    /**
     * Returns a value indicating whether there is a state of the specified name.
     * @param string $key state name
     * @return boolean whether there is a state of the specified name.
     */
    public function hasState($key)
    {
        $key=$this->getStateKeyPrefix().$key;
        return isset($_SESSION[$key]);
    }

    /**
     * Clears all user identity information from persistent storage.
     * This will remove the data stored via {@link setState}.
     */
    public function clearStates()
    {
        $keys=array_keys($_SESSION);
        $prefix=$this->getStateKeyPrefix();
        $n=strlen($prefix);
        foreach($keys as $key)
        {
            if(!strncmp($key,$prefix,$n))
                unset($_SESSION[$key]);
        }
    }

  public function hasAccessEntry($operation) {
    return Yii::app()->getAuthManager()->hasItemChild($this->model->role->itemname, $operation);
  }

  public function checkAccess($operation, $params = array(), $allowCaching=true) {
    if($allowCaching && $params===array() && isset($this->_access[$operation]))
      return $this->_access[$operation];

    $access = Yii::app()->getAuthManager()->checkAccess($operation,$this->getId(),$params);
    if($allowCaching && $params===array())
      $this->_access[$operation]=$access;

    return $access;
  }
}