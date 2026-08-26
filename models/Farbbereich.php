<?php

namespace app\models;

use Yii;

class Fachbereich extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'fachbereich';
    }

    public function rules()
    {
        return [
            [['bezeichnung'], 'required'],
            [['bezeichnung'], 'string', 'max' => 100],
            [['farbe'], 'string', 'max' => 7],
            [['bezeichnung'], 'unique'],
        ];
    }
}
