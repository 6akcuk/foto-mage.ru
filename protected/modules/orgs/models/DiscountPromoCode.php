<?php

/**
 * This is the model class for table "discount_promo_codes".
 *
 * The followings are the available columns in table 'discount_promo_codes':
 * @property integer $pc_id
 * @property integer $action_id
 * @property integer $org_id
 * @property integer $owner_id
 * @property integer $type
 * @property string $value
 * @property string $add_date
 *
 * @property User $owner
 * @property DiscountAction $action
 */
class DiscountPromoCode extends CActiveRecord
{
  const TYPE_PROMO_CODE = 0;
  const TYPE_DISCOUNT_CARD = 1;

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return DiscountPromoCode the static model class
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
		return 'discount_promo_codes';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('action_id, org_id, owner_id', 'required'),
			array('action_id, org_id, owner_id', 'numerical', 'integerOnly'=>true),
			array('value', 'length', 'max'=>50),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('pc_id, action_id, org_id, owner_id, value, add_date', 'safe', 'on'=>'search'),
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
      'action' => array(self::BELONGS_TO, 'DiscountAction', 'action_id'),
      'owner' => array(self::BELONGS_TO, 'User', 'owner_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'pc_id' => 'Pc',
			'action_id' => 'Action',
			'org_id' => 'Org',
			'owner_id' => 'Owner',
			'value' => 'Value',
			'add_date' => 'Add Date',
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

		$criteria->compare('pc_id',$this->pc_id);
		$criteria->compare('action_id',$this->action_id);
		$criteria->compare('org_id',$this->org_id);
		$criteria->compare('owner_id',$this->owner_id);
		$criteria->compare('value',$this->value,true);
		$criteria->compare('add_date',$this->add_date,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  protected function _getCode() {
    $length = 5;
    $chars = 'ABCDEFGHIJKLMNPQRSTUVWXYZ1234567890';
    $result = array();

    for ($i = 1; $i <= $length; $i++) {
      $char = $chars[mt_rand(0, strlen($chars) - 1)];
      if (mt_rand(0, 1) === 1) $char = strtoupper($char);

      $result[] = $char;
    }

    return implode('', $result);
  }

  public function generateCode() {
    $code = $this->_getCode();
    while (self::model()->find('action_id = :aid AND value = :value', array(':aid' => $this->action_id, ':value' => $code))) {
      $code = $this->_getCode();
    }

    return $code;
  }

  public function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->getIsNewRecord()) {
        $this->add_date = date("Y-m-d H:i:s");
        $this->value = $this->generateCode();
      }

      return true;
    } else {
      return false;
    }
  }
}