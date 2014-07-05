<?php

/**
 * This is the model class for table "profile_notifies".
 *
 * The followings are the available columns in table 'profile_notifies':
 * @property integer $user_id
 * @property integer $notify_im
 * @property integer $notify_orders
 * @property integer $notify_purchases
 * @property integer $notify_comments
 * @property integer $notify_payments
 */
class ProfileNotify extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ProfileNotify the static model class
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
		return 'profile_notifies';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id', 'required'),
			array('user_id, notify_im, notify_orders, notify_purchases, notify_comments, notify_payments', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('user_id, notify_im, notify_orders, notify_purchases, notify_comments, notify_payments', 'safe', 'on'=>'search'),
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
			'user_id' => 'User',
			'notify_im' => 'Личные сообщения',
			'notify_orders' => 'Полученные заказы',
			'notify_purchases' => 'Новые закупки',
			'notify_comments' => 'Комментарии к товару',
			'notify_payments' => 'Полученные платежи',
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

		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('notify_im',$this->notify_im);
		$criteria->compare('notify_orders',$this->notify_orders);
		$criteria->compare('notify_purchases',$this->notify_purchases);
		$criteria->compare('notify_comments',$this->notify_comments);
		$criteria->compare('notify_payments',$this->notify_payments);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->isNewRecord) {
        $this->notify_im = 1;
        $this->notify_orders = 1;
        $this->notify_comments = 1;
        $this->notify_payments = 1;
        $this->notify_purchases = 1;
      }

      return true;
    }
    else return false;
  }
}