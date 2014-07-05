<?php

/**
 * This is the model class for table "profile_paydetails".
 *
 * The followings are the available columns in table 'profile_paydetails':
 * @property integer $pay_id
 * @property integer $user_id
 * @property string $paysystem_name
 * @property string $paysystem_details
 */
class ProfilePaydetail extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ProfilePaydetail the static model class
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
		return 'profile_paydetails';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, paysystem_name, paysystem_details', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('paysystem_name', 'length', 'max'=>50),
			array('paysystem_details', 'length', 'max'=>200),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('pay_id, user_id, paysystem_name, paysystem_details', 'safe', 'on'=>'search'),
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
			'pay_id' => 'Pay',
			'user_id' => 'User',
			'paysystem_name' => 'Paysystem Name',
			'paysystem_details' => 'Paysystem Details',
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

		$criteria->compare('pay_id',$this->pay_id);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('paysystem_name',$this->paysystem_name,true);
		$criteria->compare('paysystem_details',$this->paysystem_details,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}