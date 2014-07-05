<?php

/**
 * This is the model class for table "user_tickets".
 *
 * The followings are the available columns in table 'user_tickets':
 * @property string $ticket_id
 * @property string $ticket_dt
 * @property integer $user_id
 * @property string $token
 */
class UserTicket extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return UserTicket the static model class
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
		return 'user_tickets';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('ticket_dt, user_id, token', 'required'),
			array('user_id', 'numerical', 'integerOnly'=>true),
			array('token', 'length', 'max'=>40),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('ticket_id, ticket_dt, user_id, token', 'safe', 'on'=>'search'),
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
			'ticket_id' => 'Ticket',
			'ticket_dt' => 'Ticket Dt',
			'user_id' => 'User',
			'token' => 'Token',
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

		$criteria->compare('ticket_id',$this->ticket_id,true);
		$criteria->compare('ticket_dt',$this->ticket_dt,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('token',$this->token,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

  public function generateToken()
  {
    $this->ticket_dt = date("Y-m-d H:i:s");
    $this->token = md5($this->user_id . $this->ticket_dt . 'x17Bj35_c6');
  }
}