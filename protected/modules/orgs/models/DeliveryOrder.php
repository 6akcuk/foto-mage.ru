<?php

/**
 * This is the model class for table "delivery_orders".
 *
 * The followings are the available columns in table 'delivery_orders':
 * @property string $order_id
 * @property integer $org_id
 * @property string $add_date
 * @property string $summary
 * @property string $currency
 * @property string $address
 * @property string $additional
 * @property string $phone
 * @property integer $owner_id
 * @property string $status
 * @property integer $persons_num
 *
 * @property DeliveryOrderItem $items
 */
class DeliveryOrder extends CActiveRecord
{
  const STATUS_PROCEEDING = 'proceeding';
  const STATUS_ACCEPTED = 'accepted';
  const STATUS_SENDED = 'sended';
  const STATUS_DELIVERED = 'delivered';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DeliveryOrder the static model class
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
		return 'delivery_orders';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('org_id, summary, currency, address, phone, owner_id, status, persons_num', 'required'),
			array('org_id, owner_id, persons_num', 'numerical', 'integerOnly'=>true),
			array('summary', 'length', 'max'=>11),
			array('currency', 'length', 'max'=>3),
			array('phone', 'length', 'max'=>20),
			array('status', 'length', 'max'=>40),
      array('address', 'length', 'max'=>250),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('order_id, org_id, add_date, summary, currency, additional, phone, owner_id, status, persons_num', 'safe', 'on'=>'search'),
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
      'items' => array(self::HAS_MANY, 'DeliveryOrderItem', 'order_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'order_id' => 'Order',
			'org_id' => 'Org',
			'add_date' => 'Дата добавления',
			'summary' => 'Сумма',
			'currency' => 'Валюта',
      'address' => 'Адрес доставки',
			'additional' => 'Дополнительно',
			'phone' => 'Контактный телефон',
			'owner_id' => 'Заказчик',
			'status' => 'Статус заказа',
			'persons_num' => 'Количество персон',
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

		$criteria->compare('order_id',$this->order_id,true);
		$criteria->compare('org_id',$this->org_id);
		$criteria->compare('add_date',$this->add_date,true);
		$criteria->compare('summary',$this->summary,true);
		$criteria->compare('currency',$this->currency,true);
		$criteria->compare('additional',$this->additional,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('owner_id',$this->owner_id);
		$criteria->compare('status',$this->status,true);
		$criteria->compare('persons_num',$this->persons_num);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->isNewRecord)
        $this->add_date = date("Y-m-d H:i:s");

      return true;
    }
    else return false;
  }
}