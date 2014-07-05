<?php

/**
 * This is the model class for table "profile_relationships".
 *
 * The followings are the available columns in table 'profile_relationships':
 * @property string $rel_id
 * @property integer $from_id
 * @property integer $to_id
 * @property integer $rel_type
 * @property string $message
 *
 * @property User $friend
 */
class ProfileRelationship extends CActiveRecord
{
    const TYPE_OUTCOME = -1;
    const TYPE_INCOME = 0;
    const TYPE_FRIENDS = 1;

    public $friend;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ProfileRelationship the static model class
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
		return 'profile_relationships';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('from_id, to_id, rel_type', 'required'),
			array('from_id, to_id, rel_type', 'numerical', 'integerOnly'=>true),
			array('message', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('rel_id, from_id, to_id, rel_type, message', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'rel_id' => 'Rel',
			'from_id' => 'From',
			'to_id' => 'To',
			'rel_type' => 'Rel Type',
			'message' => 'Message',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('rel_id',$this->rel_id,true);
		$criteria->compare('from_id',$this->from_id);
		$criteria->compare('to_id',$this->to_id);
		$criteria->compare('rel_type',$this->rel_type);
		$criteria->compare('message',$this->message,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  /**
   * Добавить в друзья
   *
   * @param User|integer $user
   * @param integer $friend_id
   * @return int
   *  Коды возврата:
   *    0 - Пользователь не найден
   *    -1 - Не удалось подтвердить заявку
   *    -2 - Заявка уже подана
   *    -3 - Не удалось отправить заявку
   *    1 - Заявка успешно отправлена
   *    2 - Теперь друзья
   */
  public static function addToFriend($user, $friend_id) {
    if (!($user instanceof User))
      $user = User::model()->with('profile')->findByPk($user);
    $friend = User::model()->with('profile')->findByPk($friend_id);

    if (!$friend) {
      return 0; // Пользователь не найден
    }

    $relation = $friend->profile->getProfileRelation();

    if ($relation) {
      if ($user->profile->isProfileRelationIncome($relation)) {
        $relation->rel_type = ProfileRelationship::TYPE_FRIENDS;
        $request = ProfileRequest::model()->find('owner_id = :id AND req_type = :type AND req_link_id = :link_id', array(
          ':id' => $user->id,
          ':type' => ProfileRequest::TYPE_FRIEND,
          ':link_id' => $relation->rel_id,
        ));
        if ($request) $request->delete();

        if (!$relation->save(true, array('rel_type'))) {
          return -1; // Не удалось подтвердить заявку
        }

        return 2; // Теперь друзья (если заявка пришла)
      }
      else return -2; // Заявка уже подана
    }
    else {
      $relation = new ProfileRelationship();
      $relation->from_id = $user->id;
      $relation->to_id = $friend_id;
      $relation->rel_type = ProfileRelationship::TYPE_OUTCOME;

      if ($relation->validate()) {
        $relation->save();

        $request = new ProfileRequest();
        $request->owner_id = $friend_id;
        $request->req_type = ProfileRequest::TYPE_FRIEND;
        $request->req_link_id = $relation->rel_id;
        if (!$request->save()) {
          $relation->delete();

          return -3; // Не удалось отправить заявку
        }

        return 1; // Заявка успешно отправлена
      }
    }
  }

  public function afterSave()
  {
    switch ($this->rel_type) {
      case ProfileRelationship::TYPE_OUTCOME:
        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->from_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->to_id);

        $sub = Subscription::model()->find($criteria);
        if (!$sub) {
          $sub = new Subscription();
          $sub->user_id = $this->from_id;
          $sub->sub_type = Subscription::TYPE_USER;
          $sub->sub_link_id = $this->to_id;
          $sub->save();
        }

        $sub = null;

        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->to_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->from_id);

        $sub = Subscription::model()->find($criteria);
        if ($sub) $sub->delete();
        break;
      case ProfileRelationship::TYPE_FRIENDS:
        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->from_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->to_id);

        $sub = Subscription::model()->find($criteria);
        if (!$sub) {
          $sub = new Subscription();
          $sub->user_id = $this->from_id;
          $sub->sub_type = Subscription::TYPE_USER;
          $sub->sub_link_id = $this->to_id;
          $sub->save();
        }

        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->to_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->from_id);

        $sub = Subscription::model()->find($criteria);
        if (!$sub) {
          $sub = new Subscription();
          $sub->user_id = $this->to_id;
          $sub->sub_type = Subscription::TYPE_USER;
          $sub->sub_link_id = $this->from_id;
          $sub->save();
        }
        break;
      case ProfileRelationship::TYPE_INCOME:
        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->from_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->to_id);

        $sub = Subscription::model()->find($criteria);
        if ($sub) $sub->delete();

        $sub = null;

        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->to_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->from_id);

        $sub = Subscription::model()->find($criteria);
        if (!$sub) {
          $sub = new Subscription();
          $sub->user_id = $this->to_id;
          $sub->sub_type = Subscription::TYPE_USER;
          $sub->sub_link_id = $this->from_id;
          $sub->save();
        }
        break;
    }
  }

  public function afterDelete() {
    switch ($this->rel_type) {
      case ProfileRelationship::TYPE_OUTCOME:
        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->from_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->to_id);

        $sub = Subscription::model()->find($criteria);
        if ($sub) $sub->delete();

        $sub = null;

        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->to_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->from_id);

        $sub = Subscription::model()->find($criteria);
        if ($sub) $sub->delete();
        break;
      case ProfileRelationship::TYPE_INCOME:
        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->from_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->to_id);

        $sub = Subscription::model()->find($criteria);
        if ($sub) $sub->delete();

        $sub = null;

        $criteria = new CDbCriteria();
        $criteria->compare('user_id', $this->to_id);
        $criteria->compare('sub_type', Subscription::TYPE_USER);
        $criteria->compare('sub_link_id', $this->from_id);

        $sub = Subscription::model()->find($criteria);
        if ($sub) $sub->delete();
        break;
    }
  }
}