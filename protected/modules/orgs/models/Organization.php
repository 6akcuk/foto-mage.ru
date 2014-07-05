<?php

/**
 * This is the model class for table "organizations".
 *
 * The followings are the available columns in table 'organizations':
 * @property integer $org_id
 * @property integer $org_type_id
 * @property string $name
 * @property integer $city_id
 * @property string $address
 * @property string $phone
 * @property string $photo
 * @property string $shortstory
 * @property string $worktimes
 * @property string $url
 * @property string $lat
 * @property string $lon
 *
 * @property OrganizationType $org_type
 * @property array|OrganizationTypeLink $types
 * @property OrganizationTypeLink $typelink
 * @property array|Room $rooms
 * @property City $city
 * @property DeliverySettings $settings
 * @property OrganizationModule $modules
 * @property array|Event $events
 */
class Organization extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Organization the static model class
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
		return 'organizations';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('org_type_id, name, city_id, address, shortstory, worktimes', 'required'),
			array('org_type_id, city_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>200),
      array('phone', 'length', 'min' => 10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('org_id, org_type_id, name, city_id, address, image, shortstory, worktimes', 'safe', 'on'=>'search'),
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
      'org_type' => array(self::HAS_ONE, 'OrganizationTypeLink', 'org_id', 'with' => 'type'),
      'types' => array(self::HAS_MANY, 'OrganizationTypeLink', 'org_id', 'with' => 'type'),
      'typelink' => array(self::BELONGS_TO, 'OrganizationTypeLink', 'org_id'),
      'rooms' => array(self::HAS_MANY, 'Room', 'org_id'),
      'city' => array(self::BELONGS_TO, 'City', 'city_id'),
      'modules' => array(self::BELONGS_TO, 'OrganizationModule', 'org_id'),
      'settings' => array(self::BELONGS_TO, 'DeliverySettings', 'org_id'),
      'events' => array(self::HAS_MANY, 'Event', 'org_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'org_id' => 'Org',
			'org_type_id' => 'Категории организации',
			'name' => 'Название',
			'city_id' => 'Город',
			'address' => 'Адрес',
      'phone' => 'Контактный телефон',
			'photo' => 'Фотография',
			'shortstory' => 'Описание',
			'worktimes' => 'Время работы',
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
		$criteria->compare('org_type_id',$this->org_type_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('city_id',$this->city_id);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('image',$this->image,true);
		$criteria->compare('shortstory',$this->shortstory,true);
		$criteria->compare('worktimes',$this->worktimes,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}