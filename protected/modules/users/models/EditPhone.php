<?php

/**
 * This is the model class for table "edit_phones".
 *
 * The followings are the available columns in table 'edit_phones':
 * @property string $edit_id
 * @property integer $owner_id
 * @property string $date
 * @property string $old_phone
 * @property string $new_phone
 * @property integer $code
 * @property string $ip
 */
class EditPhone extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return EditPhone the static model class
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
		return 'edit_phones';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('owner_id, date, old_phone, new_phone, code, ip', 'required'),
			array('owner_id, code', 'numerical', 'integerOnly'=>true),
			array('old_phone, new_phone', 'length', 'max'=>15),
			array('ip', 'length', 'max'=>10),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('edit_id, owner_id, date, old_phone, new_phone, code, ip', 'safe', 'on'=>'search'),
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
			'edit_id' => 'Edit',
			'owner_id' => 'Owner',
			'date' => 'Date',
			'old_phone' => 'Old Phone',
			'new_phone' => 'New Phone',
			'code' => 'Code',
			'ip' => 'Ip',
		);
	}

  public function generateCode() {
    return rand(100000, 999999);
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

		$criteria->compare('edit_id',$this->edit_id,true);
		$criteria->compare('owner_id',$this->owner_id);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('old_phone',$this->old_phone,true);
		$criteria->compare('new_phone',$this->new_phone,true);
		$criteria->compare('code',$this->code);
		$criteria->compare('ip',$this->ip,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}