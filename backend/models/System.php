<?php
namespace backend\models;
use yii\db\ActiveRecord;

class System extends ActiveRecord
{

    static function sendSms($phone, $text, $log = true){
        //  TODO: при отправке сообщения если log false то пишем лог того кто и что отпавил
    }
}