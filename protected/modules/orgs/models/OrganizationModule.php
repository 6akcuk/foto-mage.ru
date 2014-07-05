<?php

/**
 * This is the model class for table "organization_modules".
 *
 * The followings are the available columns in table 'organization_modules':
 * @property integer $org_id
 * @property integer $enable_delivery
 * @property integer $enable_discount
 * @property integer $enable_market
 */
class OrganizationModule extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return OrganizationModule the static model class
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
		return 'organization_modules';
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
			array('org_id, enable_delivery, enable_discount, enable_market', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('org_id, enable_delivery', 'safe', 'on'=>'search'),
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
			'enable_delivery' => 'Включить раздел Доставка',
      'enable_discount' => 'Включить раздел Дисконтная система',
      'enable_market' => 'Включить раздел Товары и услуги (Магазины)',
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
		$criteria->compare('enable_delivery',$this->enable_delivery);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}