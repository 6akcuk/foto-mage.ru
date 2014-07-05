<?php

/**
 * This is the model class for table "profiles".
 *
 * The followings are the available columns in table 'profiles':
 * @property integer $user_id
 * @property string $lastname
 * @property string $firstname
 * @property string $middlename
 * @property string $phone
 * @property string $photo
 * @property integer $city_id
 * @property integer $positive_rep
 * @property integer $negative_rep
 * @property string $status
 * @property integer $email_notify
 * @property integer $sms_notify
 * @property string $about
 * @property string $gender
 *
 * @property City $city
 * @property ProfilePaydetail $paydetails
 * @property ProfileNotify $notifies
 */
class Profile extends CActiveRecord
{
    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Profile the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'profiles';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, firstname, lastname, gender', 'required'),
			array('user_id, city_id, positive_rep, negative_rep, email_notify, sms_notify', 'numerical', 'integerOnly'=>true),
      array('photo', 'safe'),
			array('lastname', 'length', 'max'=>128),
      array('firstname, middlename', 'length', 'max'=>64),
			array('phone', 'length', 'max'=>32),
			array('status, about', 'length', 'max'=>255),
			array('gender', 'length', 'max'=>6),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, lastname, firstname, middlename, phone, photo, city_id, positive_rep, negative_rep, status, email_notify, sms_notify, about, gender', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
      'city' => array(self::HAS_ONE, 'City', array('id' => 'city_id')),
      'paydetails' => array(self::HAS_MANY, 'ProfilePaydetail', array('user_id' => 'user_id')),
      'notifies' => array(self::HAS_ONE, 'ProfileNotify', 'user_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'user_id' => 'User',
			'lastname' => 'Фамилия',
			'firstname' => 'Имя',
			'middlename' => 'Отчество',
			'phone' => 'Моб. телефон',
			'photo' => 'Фотография',
			'city_id' => 'Город',
			'positive_rep' => 'Positive Rep',
			'negative_rep' => 'Negative Rep',
			'status' => 'Статус',
			'email_notify' => 'Получать оповещения по электронной почте',
			'sms_notify' => 'Получать SMS-оповещения',
			'about' => 'О себе',
			'gender' => 'Пол',
		);
	}

  public function getProfileImage($size = 'b') {
    $width = 'auto';
    $height = 'auto';

    return ($this->photo) ? ActiveHtml::showUploadImage($this->photo, $size) : '<img src="/images/camera_'. $size .'.gif" width="'. $width .'" height="'. $height .'" />';
  }

    public function genderToInt() {
        return ($this->gender == 'Male') ? self::GENDER_MALE : self::GENDER_FEMALE;
    }

    public function countFriends() {
        return ProfileRelationship::model()->count('(from_id = :id OR to_id = :id) AND rel_type = :type', array(
            ':id' => $this->user_id,
            ':type' => ProfileRelationship::TYPE_FRIENDS,
        ));
    }

    public function countAllFriends($c = array()) {
        $where = array();
        $conn = $this->getDbConnection();

        if (isset($c['name'])) {
            $where[] = (Yii::app()->user->checkAccess('global.fullnameView'))
                ? "(friend.login LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ." OR profile.lastname LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ." OR profile.firstname LIKE ". $conn->quoteValue('%'. $c['name'] .'%') .")"
                : "friend.login LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ."";
        }

        if (isset($c['city_id']) && $c['city_id']) {
            $where[] = 'profile.city_id = '. intval($c['city_id']);
        }

        if (isset($c['role']) && $c['role']) {
            $where[] = 'rbac.itemname = '. $conn->quoteValue($c['role']);
        }

        $where[] = 't.rel_type = '. ProfileRelationship::TYPE_FRIENDS;

        $command = $conn->createCommand('
        SELECT (
        (SELECT COUNT(*) AS num FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE '. implode(' AND ', array_merge($where, array('t.from_id = '. $this->user_id))) .')
        +
        (SELECT COUNT(*) AS num FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.from_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE '. implode(' AND ', array_merge($where, array('t.to_id = '. $this->user_id))) .')
        ) AS total');

        $row = $command->queryRow();
        return $row['total'];
    }

    public function getAllFriends($c = array(), $offset = 0) {
        $where = array();
        $conn = $this->getDbConnection();

        if (isset($c['name'])) {
            $where[] = "(friend.login LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ." OR profile.lastname LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ." OR profile.firstname LIKE ". $conn->quoteValue('%'. $c['name'] .'%') .")";
        }

        if (isset($c['city_id']) && $c['city_id']) {
            $where[] = 'profile.city_id = '. intval($c['city_id']);
        }

        if (isset($c['role']) && $c['role']) {
            $where[] = 'rbac.itemname = '. $conn->quoteValue($c['role']);
        }

        $where[] = 't.rel_type = '. ProfileRelationship::TYPE_FRIENDS;

        $command = $conn->createCommand('SELECT t.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE '. implode(' AND ', array_merge($where, array('t.from_id = '. $this->user_id))) .' LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage .'
            UNION
                SELECT t.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_relationships` AS t
                    INNER JOIN `users` AS friend ON friend.id = t.from_id
                        INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                        INNER JOIN `cities` AS city ON city.id = profile.city_id
                        INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                            WHERE '. implode(' AND ', array_merge($where, array('t.to_id = '. $this->user_id))) .' LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage);

        return $this->_friendReader($command->query());
    }

    public function countOnlineFriends($c = array()) {
        $where = array();
        $conn = $this->getDbConnection();

        if (isset($c['name'])) {
            $where[] = (Yii::app()->user->checkAccess('global.fullnameView'))
                ? "(friend.login LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ." OR profile.lastname LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ." OR profile.firstname LIKE ". $conn->quoteValue('%'. $c['name'] .'%') .")"
                : "friend.login LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ."";
        }

        if (isset($c['city_id']) && $c['city_id']) {
            $where[] = 'profile.city_id = '. intval($c['city_id']);
        }

        if (isset($c['role']) && $c['role']) {
            $where[] = 'rbac.itemname = '. $conn->quoteValue($c['role']);
        }

        $where[] = 't.rel_type = '. ProfileRelationship::TYPE_FRIENDS;
        $where[] = 'friend.lastvisit >= NOW() - INTERVAL '. Yii::app()->getModule('users')->onlineInterval .' MINUTE';

        $command = $conn->createCommand('
        SELECT (
        (SELECT COUNT(*) AS num FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
            INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
            INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                WHERE '. implode(' AND ', array_merge($where, array('t.from_id = '. $this->user_id))) .')
        +
        (SELECT COUNT(*) AS num FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.from_id
            INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
            INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                WHERE '. implode(' AND ', array_merge($where, array('t.to_id = '. $this->user_id))) .')
        ) AS total');

        $row = $command->queryRow();
        return $row['total'];
    }

    public function getOnlineFriends($c = array(), $offset = 0) {
        $where = array();
        $conn = $this->getDbConnection();

        if (isset($c['name'])) {
            $where[] = (Yii::app()->user->checkAccess('global.fullnameView'))
                ? "(friend.login LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ." OR profile.lastname LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ." OR profile.firstname LIKE ". $conn->quoteValue('%'. $c['name'] .'%') .")"
                : "friend.login LIKE ". $conn->quoteValue('%'. $c['name'] .'%') ."";
        }

        if (isset($c['city_id']) && $c['city_id']) {
            $where[] = 'profile.city_id = '. intval($c['city_id']);
        }

        if (isset($c['role']) && $c['role']) {
            $where[] = 'rbac.itemname = '. $conn->quoteValue($c['role']);
        }

        $where[] = 't.rel_type = '. ProfileRelationship::TYPE_FRIENDS;
        $where[] = 'friend.lastvisit >= NOW() - INTERVAL '. Yii::app()->getModule('users')->onlineInterval .' MINUTE';

        $command = $conn->createCommand('SELECT t.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE '. implode(' AND ', array_merge($where, array('t.from_id = '. $this->user_id))) .' LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage .'
            UNION
                SELECT t.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_relationships` AS t
                    INNER JOIN `users` AS friend ON friend.id = t.from_id
                        INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                        INNER JOIN `cities` AS city ON city.id = profile.city_id
                        INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                            WHERE '. implode(' AND ', array_merge($where, array('t.to_id = '. $this->user_id))) .' LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage);

        return $this->_friendReader($command->query());
    }

    protected function _friendReader(CDbDataReader $dataReader) {
        $result = array();

        while (($row = $dataReader->read()) !== false) {
            $relation = new ProfileRelationship();
            $relation->attributes = $row;

            $friend = new User();
            $friend->attributes = $row;
            $friend->id = $row['id'];
            $friend->lastvisit = $row['lastvisit'];

            $friend->role = new RbacAssignment();
            $friend->role->itemname = $row['itemname'];
            $friend->role->userid = $row['userid'];

            $profile = new Profile();
            $profile->attributes = $row;

            $profile->city = new City();
            $profile->city->id = $row['city_id'];
            $profile->city->name = $row['city_name'];

            $friend->profile = $profile;

            $relation->friend = $friend;

            $result[] = $relation;
        }

        return $result;
    }

    public function getFriends() {
        $result = array();
        $conn = $this->getDbConnection();
        $command = $conn->createCommand('SELECT t.*, friend.*, profile.* FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                    WHERE t.from_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_FRIENDS .' LIMIT 6
            UNION
                SELECT t.*, friend.*, profile.* FROM `profile_relationships` AS t
                    INNER JOIN `users` AS friend ON friend.id = t.from_id
                        INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                            WHERE t.to_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_FRIENDS .' LIMIT 6');

        $dataReader = $command->query();

        while (($row = $dataReader->read()) !== false) {
            $relation = new ProfileRelationship();
            $relation->attributes = $row;

            $friend = new User();
            $friend->attributes = $row;
            $friend->id = $row['id'];

            $profile = new Profile();
            $profile->attributes = $row;

            $friend->profile = $profile;

            $relation->friend = $friend;

            $result[] = $relation;
        }

        return $result;
    }

    public function getCurrentFriendRequests($offset = 0) {
        $conn = $this->getDbConnection();
        $command = $conn->createCommand('
        SELECT t.*, rel.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_requests` AS t
            INNER JOIN `profile_relationships` AS rel ON rel.rel_id = t.req_link_id AND rel.from_id = t.owner_id
                INNER JOIN `users` AS friend ON friend.id = rel.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE t.owner_id = '. $this->user_id .' AND t.req_type = '. ProfileRequest::TYPE_FRIEND .' AND t.viewed = 0
                        AND rel.rel_type = '. ProfileRelationship::TYPE_INCOME .'
                        LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage .'
        UNION
        SELECT t.*, rel.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_requests` AS t
            INNER JOIN `profile_relationships` AS rel ON rel.rel_id = t.req_link_id AND rel.to_id = t.owner_id
                INNER JOIN `users` AS friend ON friend.id = rel.from_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE t.owner_id = '. $this->user_id .' AND t.req_type = '. ProfileRequest::TYPE_FRIEND .' AND t.viewed = 0
                        AND rel.rel_type = '. ProfileRelationship::TYPE_OUTCOME .'
                        LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage .'');

        return $this->_friendReader($command->query());
    }

    public function countAllSubscribers() {
        $conn = $this->getDbConnection();

        $command = $conn->createCommand('
        SELECT (
        (SELECT COUNT(*) AS num FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE t.from_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_INCOME .')
        +
        (SELECT COUNT(*) AS num FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.from_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE t.to_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_OUTCOME .')
        ) AS total');

        $row = $command->queryRow();
        return $row['total'];
    }

    public function getAllSubscribers($offset = 0) {
        $conn = $this->getDbConnection();

        $command = $conn->createCommand('SELECT t.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE t.from_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_INCOME .' LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage .'
            UNION
                SELECT t.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_relationships` AS t
                    INNER JOIN `users` AS friend ON friend.id = t.from_id
                        INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                        INNER JOIN `cities` AS city ON city.id = profile.city_id
                        INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                            WHERE t.to_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_OUTCOME .' LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage);

        return $this->_friendReader($command->query());
    }

    public function countAllOutFriendRequests() {
        $conn = $this->getDbConnection();

        $command = $conn->createCommand('
        SELECT (
        (SELECT COUNT(*) AS num FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE t.from_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_OUTCOME .')
        +
        (SELECT COUNT(*) AS num FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.from_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE t.to_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_INCOME .')
        ) AS total');

        $row = $command->queryRow();
        return $row['total'];
    }

    public function getAllOutFriendRequests($offset = 0) {
        $conn = $this->getDbConnection();

        $command = $conn->createCommand('SELECT t.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_relationships` AS t
            INNER JOIN `users` AS friend ON friend.id = t.to_id
                INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                INNER JOIN `cities` AS city ON city.id = profile.city_id
                INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                    WHERE t.from_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_OUTCOME .' LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage .'
            UNION
                SELECT t.*, friend.*, profile.*, city.id AS city_id, city.name AS city_name, rbac.* FROM `profile_relationships` AS t
                    INNER JOIN `users` AS friend ON friend.id = t.from_id
                        INNER JOIN `profiles` AS profile ON profile.user_id = friend.id
                        INNER JOIN `cities` AS city ON city.id = profile.city_id
                        INNER JOIN `rbac_assignments` AS rbac ON rbac.userid = friend.id
                            WHERE t.to_id = '. $this->user_id .' AND t.rel_type = '. ProfileRelationship::TYPE_INCOME .' LIMIT '. $offset .', '. Yii::app()->getModule('users')->friendsPerPage);

        return $this->_friendReader($command->query());
    }

    /**
     * @return ProfileRelationship
     */
    public function getProfileRelation() {
        /** @var $relation ProfileRelationship */
        return ProfileRelationship::model()->find('(from_id = :id AND to_id = :my) OR (from_id = :my AND to_id = :id)', array(
            ':id' => $this->user_id,
            ':my' => Yii::app()->user->getId(),
        ));
    }

    public function isFriend(ProfileRelationship $relationship) {
        if ($relationship->rel_type == ProfileRelationship::TYPE_FRIENDS) return true;
        else return false;
    }

    public function isProfileRelationIncome(ProfileRelationship $relationship) {
        if (($relationship->rel_type == ProfileRelationship::TYPE_INCOME && $relationship->from_id == $this->user_id) ||
                    ($relationship->rel_type == ProfileRelationship::TYPE_OUTCOME && $relationship->to_id == $this->user_id))
            return true;
        else return false;
    }

    public function isProfileRelationOutcome(ProfileRelationship $relationship) {
        if (($relationship->rel_type == ProfileRelationship::TYPE_OUTCOME && $relationship->from_id == $this->user_id) ||
            ($relationship->rel_type == ProfileRelationship::TYPE_INCOME && $relationship->to_id == $this->user_id))
            return true;
        else return false;
    }
}