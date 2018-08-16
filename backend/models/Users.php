<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "users".
 *
 * @property int $id
 * @property string $login
 * @property string $phone
 * @property int $telegramm
 * @property string $email
 * @property string $first_name
 * @property string $second_name
 * @property string $last_name
 * @property int $gender
 * @property string $birthday
 * @property string $password_reset_token
 * @property string $password_hash
 * @property string $auth_key
 * @property string $enter_key
 * @property int $time_out
 * @property string $created_at
 * @property string $updated_at
 * @property int $create_by_user
 * @property int $update_by_user
 * @property int $status
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthItem[] $itemNames
 */
class Users extends \yii\db\ActiveRecord
{

    public static function tableName()
    {
        return 'users';
    }

    public static function getDb(){
        return \Yii::$app->db;
    }


    public function rules()
    {
        return [
            [['login', 'phone', 'first_name', 'second_name', 'last_name', 'gender', 'password_reset_token', 'password_hash', 'auth_key', 'enter_key', 'create_by_user'], 'required'],
            [['telegramm', 'gender', 'time_out', 'create_by_user', 'update_by_user', 'status'], 'integer'],
            [['birthday', 'created_at', 'updated_at'], 'safe'],
            [['login', 'first_name', 'second_name', 'last_name'], 'string', 'max' => 64],
            [['phone'], 'string', 'max' => 12],
            [['email'], 'string', 'max' => 128],
            [['password_reset_token', 'password_hash', 'auth_key'], 'string', 'max' => 255],
            [['enter_key'], 'string', 'max' => 60],
        ];

        return [
            [['login', 'phone', 'first_name', 'second_name', 'shop_id'], 'required'],
            [['telegramm', 'gender', 'time_out', 'create_by_user', 'update_by_user', 'status'], 'integer'],
            [['birthday', 'created_at', 'updated_at'], 'safe'],
            [['login', 'email', 'first_name', 'second_name', 'last_name', 'password_reset_token', 'password_hash', 'auth_key','roleName'], 'string', 'max' => 255],
            [['phone'], 'string', 'max' => 12],
            [['enter_key'], 'string', 'max' => 60],
            [['phone'], 'unique'],
            [['login'], 'unique'],
            [['telegramm'], 'unique'],
            [['passwordNew', 'confirmPassword'], 'validPass'],
            [['update_by_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['update_by_user' => 'id']],
            [['create_by_user'], 'exist', 'skipOnError' => true, 'targetClass' => Users::className(), 'targetAttribute' => ['create_by_user' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'phone' => 'Phone',
            'telegramm' => 'Telegramm',
            'email' => 'Email',
            'first_name' => 'First Name',
            'second_name' => 'Second Name',
            'last_name' => 'Last Name',
            'gender' => 'Gender',
            'birthday' => 'Birthday',
            'password_reset_token' => 'Password Reset Token',
            'password_hash' => 'Password Hash',
            'auth_key' => 'Auth Key',
            'enter_key' => 'Enter Key',
            'time_out' => 'Time Out',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'create_by_user' => 'Create By User',
            'update_by_user' => 'Update By User',
            'status' => 'Status',
        ];
    }


    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::className(), ['user_id' => 'id']);
    }


    public function getItemNames()
    {
        return $this->hasMany(AuthItem::className(), ['name' => 'item_name'])->viaTable('auth_assignment', ['user_id' => 'id']);
    }

    public function getRole(){
        return $this->hasOne(AuthAssignment::className(), ['user_id' => 'id']);
    }

    public function getShop(){
        return $this->hasOne(Shop::className(), ['id' => 'shop_id']);
    }

    public function validPass($attribute, $params){
        // валидация пароля и поставить hash
        if(strlen($this->passwordNew)>0){
            if(strlen($this->confirmPassword)>0){
                if(strcmp($this->confirmPassword, $this->passwordNew)==0) {
                    $this->password_hash = Yii::$app->security->generatePasswordHash($this->passwordNew);
                    $this->auth_key = Yii::$app->security->generateRandomString();
                }
                else{
                    $this->addError('confirmPassword', 'Пароли не совпадают');
                }
            }
            else{
                $this->addError('confirmPassword', 'Введите подтверждение пароля');
            }
        }
        else{
            $this->addError('passwordNew', 'Введите пароль');
        }
        $this->passwordNew='';
        $this->confirmPassword='';
    }
    public static function getUserByPhone($phone = null){
        if(!empty($phone)) {
            $user = Users::find()->where(['phone' => $phone])->one();
            if(!empty($user))
                return $user;
            else
                return false;
        }
        else
            return false;
    }
}
