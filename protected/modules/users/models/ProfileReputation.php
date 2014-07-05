<?php

/**
 * This is the model class for table "profile_reputations".
 *
 * The followings are the available columns in table 'profile_reputations':
 * @property string $rep_id
 * @property string $rep_date
 * @property integer $author_id
 * @property integer $owner_id
 * @property integer $value
 * @property string $comment
 * @property string $reputation_delete
 */
class ProfileReputation extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ProfileReputation the static model class
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
		return 'profile_reputations';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('author_id, owner_id, value, comment', 'required'),
			array('author_id, owner_id, value', 'numerical', 'integerOnly'=>true),
			array('comment', 'length', 'max'=>255),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('rep_id, rep_date, author_id, owner_id, value, comment', 'safe', 'on'=>'search'),
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
            'author' => array(self::BELONGS_TO, 'User', 'author_id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'rep_id' => 'Rep',
			'rep_date' => 'Rep Date',
			'author_id' => 'Author',
			'owner_id' => 'Owner',
			'value' => 'Value',
			'comment' => 'Comment',
		);
	}

    public function defaultScope() {
        return array(
            'condition' => "reputation_delete IS NULL",
        );
    }

    public function beforeSave() {
        if (parent::beforeSave()) {
            if ($this->isNewRecord)
                $this->rep_date = date("Y-m-d H:i:s");

            return true;
        }
        else return false;
    }
}