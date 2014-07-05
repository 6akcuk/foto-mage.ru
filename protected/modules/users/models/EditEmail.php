<?php

/**
 * This is the model class for table "edit_emails".
 *
 * The followings are the available columns in table 'edit_emails':
 * @property string $edit_id
 * @property integer $owner_id
 * @property string $date
 * @property string $ip
 * @property string $old_mail
 * @property string $new_mail
 * @property string $hash
 */
class EditEmail extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return EditEmail the static model class
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
		return 'edit_emails';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('owner_id, date, ip, old_mail, new_mail, hash', 'required'),
			array('owner_id', 'numerical', 'integerOnly'=>true),
			array('ip', 'length', 'max'=>10),
			array('old_mail, new_mail', 'length', 'max'=>50),
			array('hash', 'length', 'max'=>32),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('edit_id, owner_id, date, ip, old_mail, new_mail, hash', 'safe', 'on'=>'search'),
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
			'ip' => 'Ip',
			'old_mail' => 'Old Mail',
			'new_mail' => 'New Mail',
			'hash' => 'Hash',
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

		$criteria->compare('edit_id',$this->edit_id,true);
		$criteria->compare('owner_id',$this->owner_id);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('ip',$this->ip,true);
		$criteria->compare('old_mail',$this->old_mail,true);
		$criteria->compare('new_mail',$this->new_mail,true);
		$criteria->compare('hash',$this->hash,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
}