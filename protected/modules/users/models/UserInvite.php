<?php

/**
 * This is the model class for table "user_invites".
 *
 * The followings are the available columns in table 'user_invites':
 * @property integer $master_id
 * @property integer $child_id
 * @property string $datetime
 * @property string $invite_id
 */
class UserInvite extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserInvite the static model class
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
		return 'user_invites';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('master_id, child_id, datetime', 'required'),
			array('master_id, child_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('master_id, child_id, datetime, invite_id', 'safe', 'on'=>'search'),
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
			'master_id' => 'Master',
			'child_id' => 'Child',
			'datetime' => 'Datetime',
			'invite_id' => 'Invite',
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

		$criteria->compare('master_id',$this->master_id);
		$criteria->compare('child_id',$this->child_id);
		$criteria->compare('datetime',$this->datetime,true);
		$criteria->compare('invite_id',$this->invite_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}