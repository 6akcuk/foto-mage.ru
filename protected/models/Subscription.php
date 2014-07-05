<?php

/**
 * This is the model class for table "subscriptions".
 *
 * The followings are the available columns in table 'subscriptions':
 * @property string $sub_id
 * @property integer $user_id
 * @property string $sub_type
 * @property string $sub_link_id
 */
class Subscription extends CActiveRecord
{
  const TYPE_USER = 'user';
  const TYPE_PURCHASE = 'purchase';
  const TYPE_GOOD = 'good';
  const TYPE_CITY = 'city';
  const TYPE_WALL_POST = 'post';
  const TYPE_THEME_POST = 'theme';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Subscription the static model class
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
		return 'subscriptions';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, sub_type, sub_link_id', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('sub_type', 'length', 'max'=>40),
			array('sub_link_id', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('sub_id, user_id, sub_type, sub_link_id', 'safe', 'on'=>'search'),
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
			'sub_id' => 'Sub',
			'user_id' => 'User',
			'sub_type' => 'Sub Type',
			'sub_link_id' => 'Sub Link',
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

		$criteria->compare('sub_id',$this->sub_id,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('sub_type',$this->sub_type,true);
		$criteria->compare('sub_link_id',$this->sub_link_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}