<?php

/**
 * This is the model class for table "import_data".
 *
 * The followings are the available columns in table 'import_data':
 * @property integer $id
 * @property integer $author_id
 * @property string $date
 * @property string $type
 * @property string $filename
 * @property string $data
 * @property integer $completed
 * @property integer $total
 * @property string $status
 */
class ImportData extends CActiveRecord
{
  const TYPE_ORG = 'Org';

  const STATUS_UPLOADED = 'Uploaded';
  const STATUS_PROCESS = 'Process';
  const STATUS_COMPLETED = 'Completed';

	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return ImportData the static model class
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
		return 'import_data';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('author_id, type, filename, total', 'required'),
			array('id, author_id, completed, total', 'numerical', 'integerOnly'=>true),
			array('type', 'length', 'max'=>100),
			array('filename', 'length', 'max'=>200),
			array('status', 'length', 'max'=>9),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('id, author_id, date, type, filename, completed, total, status', 'safe', 'on'=>'search'),
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
			'author_id' => 'Author',
			'date' => 'Дата',
			'type' => 'Тип',
			'filename' => 'Файл',
			'completed' => 'Импортировано',
			'total' => 'Всего',
			'status' => 'Статус',
		);
	}

  protected function beforeSave() {
    if (parent::beforeSave()) {
      if ($this->getIsNewRecord()) {
        $this->date = date("Y-m-d H:i:s");
        $this->status = self::STATUS_UPLOADED;
      }

      return true;
    }
    else return false;
  }
}