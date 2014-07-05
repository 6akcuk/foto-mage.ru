<?php

/**
 * This is the model class for table "delivery_goods".
 *
 * The followings are the available columns in table 'delivery_goods':
 * @property string $good_id
 * @property string $market_id
 * @property integer $org_id
 * @property integer $element_id
 * @property string $name
 * @property string $facephoto
 * @property string $price
 * @property string $currency
 * @property string $discount
 * @property string $shortstory
 *
 * @property Organization $org
 */
class DeliveryGood extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DeliveryGood the static model class
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
		return 'delivery_goods';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('org_id, element_id, name, facephoto, price, currency, shortstory', 'required'),
			array('org_id, element_id', 'numerical', 'integerOnly'=>true),
			array('market_id, price', 'length', 'max'=>10),
			array('name', 'length', 'max'=>200),
			array('currency', 'length', 'max'=>3),
      array('discount', 'length', 'max'=>15),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('good_id, market_id, org_id, element_id, name, facephoto, price, currency, shortstory', 'safe', 'on'=>'search'),
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
      'element' => array(self::BELONGS_TO, 'DeliveryMenuElement', 'element_id'),
      'org' => array(self::BELONGS_TO, 'Organization', 'org_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'good_id' => 'Good',
			'market_id' => 'Market',
			'org_id' => 'Org',
			'element_id' => 'Элемент меню',
			'name' => 'Наименование товара',
			'facephoto' => 'Основное изображение',
			'price' => 'Цена',
			'currency' => 'Валюта',
			'shortstory' => 'Описание',
      'discount' => 'Скидка',
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

		$criteria->compare('good_id',$this->good_id,true);
		$criteria->compare('market_id',$this->market_id,true);
		$criteria->compare('org_id',$this->org_id);
		$criteria->compare('element_id',$this->element_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('facephoto',$this->facephoto,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('currency',$this->currency,true);
		$criteria->compare('shortstory',$this->shortstory,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}