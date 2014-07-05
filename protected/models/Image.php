<?php

/**
 * This is the model class for table "images".
 *
 * The followings are the available columns in table 'images':
 * @property string $image_id
 * @property integer $user_id
 * @property string $adddate
 * @property integer $aid
 * @property integer $server_id
 * @property string $folder
 * @property string $filename
 * @property integer $width
 * @property integer $height
 * @property integer $size
 * @property integer $useful
 */
class Image extends CActiveRecord
{
	/**
	 * Returns the static model of the specified AR class.
	 * @param string $className active record class name.
	 * @return Image the static model class
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
		return 'images';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('user_id, server_id, folder, filename, width, height, size', 'required'),
			array('user_id, aid, server_id, width, height, size', 'numerical', 'integerOnly'=>true),
			array('folder, filename', 'length', 'max'=>20),
			// The following rule is used by search().
			// Please remove those attributes that should not be searched.
			array('image_id, user_id, aid, server_id, folder, filename, width, height, size', 'safe', 'on'=>'search'),
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
			'image_id' => 'Image',
			'user_id' => 'User',
			'aid' => 'Aid',
			'server_id' => 'Server',
			'folder' => 'Folder',
			'filename' => 'Filename',
			'width' => 'Width',
			'height' => 'Height',
			'size' => 'Size',
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

		$criteria->compare('image_id',$this->image_id,true);
		$criteria->compare('user_id',$this->user_id);
		$criteria->compare('aid',$this->aid);
		$criteria->compare('server_id',$this->server_id);
		$criteria->compare('folder',$this->folder,true);
		$criteria->compare('filename',$this->filename,true);
		$criteria->compare('width',$this->width);
		$criteria->compare('height',$this->height);
		$criteria->compare('size',$this->size);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

    public function beforeSave() {
        if (parent::beforeSave()) {
            if ($this->getIsNewRecord())
                $this->adddate = date("Y-m-d H:i:s");

            return true;
        }
        else return false;
    }
}