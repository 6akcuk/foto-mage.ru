<?php

/**
 * This is the model class for table "profile_requests".
 *
 * The followings are the available columns in table 'profile_requests':
 * @property string $req_id
 * @property integer $owner_id
 * @property integer $req_type
 * @property integer $req_link_id
 * @property integer $viewed
 */
class ProfileRequest extends CActiveRecord
{
  const TYPE_FRIEND = 0;
  const TYPE_PM = 1;
  const TYPE_WALL_ANSWER = 2;
  const TYPE_COMMENT_ANSWER = 3;
  const TYPE_COMMENT = 4;
  const TYPE_POST_ON_WALL = 5;
  const TYPE_DISCUSS_POST_ANSWER = 6;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ProfileRequest the static model class
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
		return 'profile_requests';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('owner_id, req_type, req_link_id', 'required'),
			array('owner_id, req_type, req_link_id, viewed', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('req_id, owner_id, req_type, req_link_id, viewed', 'safe', 'on'=>'search'),
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
			'req_id' => 'Req',
			'owner_id' => 'Owner',
			'req_type' => 'Req Type',
			'req_link_id' => 'Req Link',
			'viewed' => 'Viewed',
		);
	}

  public function beforeSave() {
      if (parent::beforeSave()) {
          if ($this->getIsNewRecord())
              $this->viewed = 0;

          return true;
      }
      else return false;
  }
}