<?php

/**
 * This is the model class for table "support".
 *
 * The followings are the available columns in table 'support':
 * @property integer $id
 * @property string $date
 * @property string $name
 * @property string $email
 * @property string $msg
 * @property string $status
 */
class Support extends CActiveRecord
{
  const STATUS_SENDED = 'Sended';
  const STATUS_RECEIVED = 'Received';
  const STATUS_PROCESSED = 'Processed';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Support the static model class
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
		return 'support';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, email, msg', 'required'),
      array('msg', 'length', 'min' => 20),
      array('email', 'email'),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, name', 'safe', 'on'=>'search'),
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
			'id' => 'ID',
      'date' => 'Дата опубликования',
			'name' => 'Имя',
      'email' => 'E-Mail',
      'msg' => 'Сообщение',
      'status' => 'Статус',
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

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->getIsNewRecord()) {
        $this->date = date("Y-m-d H:i:s");
        $this->status = self::STATUS_SENDED;
      }
      return true;
    } else return false;
  }
}