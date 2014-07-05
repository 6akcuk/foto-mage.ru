<?php

/**
 * This is the model class for table "market_goods".
 *
 * The followings are the available columns in table 'market_goods':
 * @property string $good_id
 * @property integer $org_id
 * @property integer $category_id
 * @property string $name
 * @property string $facephoto
 * @property string $price
 * @property string $currency
 * @property string $discount
 * @property string $shortstory
 *
 * @property GoodCategory $category
 */
class MarketGood extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return MarketGood the static model class
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
		return 'market_goods';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('org_id, category_id, name, facephoto, price, currency, shortstory', 'required'),
			array('org_id, category_id', 'numerical', 'integerOnly'=>true),
			array('name', 'length', 'max'=>200),
			array('price', 'length', 'max'=>10),
			array('currency', 'length', 'max'=>3),
			array('discount', 'length', 'max'=>15),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('good_id, org_id, category_id, name, facephoto, price, currency, discount, shortstory', 'safe', 'on'=>'search'),
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
      'category' => array(self::BELONGS_TO, 'GoodCategory', 'category_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'good_id' => 'Good',
			'org_id' => 'Org',
			'category_id' => 'Категория товара/услуги',
			'name' => 'Наименование товара/услуги',
			'facephoto' => 'Изображение',
			'price' => 'Цена',
			'currency' => 'Currency',
			'discount' => 'Скидка',
			'shortstory' => 'Краткое описание',
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
		$criteria->compare('org_id',$this->org_id);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('facephoto',$this->facephoto,true);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('currency',$this->currency,true);
		$criteria->compare('discount',$this->discount,true);
		$criteria->compare('shortstory',$this->shortstory,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}