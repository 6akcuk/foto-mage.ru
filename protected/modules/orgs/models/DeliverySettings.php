<?php

/**
 * This is the model class for table "delivery_settings".
 *
 * The followings are the available columns in table 'delivery_settings':
 * @property integer $org_id
 * @property string $fullstory
 * @property string $logo
 * @property string $sms_phone
 * @property string $discount_flyer
 * @property integer $disable_cart
 */
class DeliverySettings extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DeliverySettings the static model class
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
		return 'delivery_settings';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('org_id', 'required'),
			array('org_id', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('org_id, fullstory, logo', 'safe', 'on'=>'search'),
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
			'org_id' => 'Org',
			'fullstory' => 'Дополнительная информация по доставке',
			'logo' => 'Логотип компании в меню доставки',
      'sms_phone' => 'Телефон для приема заказов по СМС',
      'discount_flyer' => 'Флаер единой дисконтной системы',
      'disable_cart' => 'Отключить возможность заказов',
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

		$criteria->compare('org_id',$this->org_id);
		$criteria->compare('fullstory',$this->fullstory,true);
		$criteria->compare('logo',$this->logo,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}