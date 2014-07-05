<?php

/**
 * This is the model class for table "delivery_order_item".
 *
 * The followings are the available columns in table 'delivery_order_item':
 * @property string $item_id
 * @property string $order_id
 * @property string $good_name
 * @property string $good_photo
 * @property integer $amount
 * @property string $price
 * @property string $currency
 */
class DeliveryOrderItem extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DeliveryOrderItem the static model class
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
		return 'delivery_order_item';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('order_id, good_name, good_photo, amount, price, currency', 'required'),
			array('amount', 'numerical', 'integerOnly'=>true),
			array('order_id', 'length', 'max'=>20),
			array('price', 'length', 'max'=>11),
			array('currency', 'length', 'max'=>3),
      array('good_name', 'length', 'max'=>150),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('item_id, order_id, amount, price, currency', 'safe', 'on'=>'search'),
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
			'item_id' => 'Item',
			'order_id' => 'Номер заказа',
			'good_name' => 'Наименование товара',
      'good_photo' => 'Изображение товара',
			'amount' => 'Количество',
			'price' => 'Цена',
			'currency' => 'Валюта',
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

		$criteria->compare('item_id',$this->item_id,true);
		$criteria->compare('order_id',$this->order_id,true);
		$criteria->compare('good_id',$this->good_id);
		$criteria->compare('amount',$this->amount);
		$criteria->compare('price',$this->price,true);
		$criteria->compare('currency',$this->currency,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}