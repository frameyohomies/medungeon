<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "produkt".
 *
 * @property int $id
 * @property string $name
 * @property string|null $barcode
 * @property string|null $standort
 * @property int $quantitaet
 * @property int $mindestbestand
 * @property string $erstellt_am
 * @property string $aktualisiert_am
 *
 * @property BestandBewegung[] $bestandBewegungs
 */
class Produkt extends \yii\db\ActiveRecord
{


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'produkt';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['barcode', 'standort'], 'default', 'value' => null],
            [['mindestbestand'], 'default', 'value' => 0],
            [['name'], 'required'],
            [['quantitaet', 'mindestbestand'], 'integer'],
            [['erstellt_am', 'aktualisiert_am'], 'safe'],
            [['name', 'standort'], 'string', 'max' => 150],
            [['barcode'], 'string', 'max' => 64],
            [['barcode'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'barcode' => Yii::t('app', 'Barcode'),
            'standort' => Yii::t('app', 'Standort'),
            'quantitaet' => Yii::t('app', 'Quantitaet'),
            'mindestbestand' => Yii::t('app', 'Mindestbestand'),
            'erstellt_am' => Yii::t('app', 'Erstellt Am'),
            'aktualisiert_am' => Yii::t('app', 'Aktualisiert Am'),
        ];
    }

    /**
     * Gets query for [[BestandBewegungs]].
     *
     * @return \yii\db\ActiveQuery|BestandBewegungQuery
     */
    public function getBestandBewegungs()
    {
        return $this->hasMany(BestandBewegung::class, ['produkt_id' => 'id']);
    }

    public function getFachbereich()
    {
        return $this->hasOne(Fachbereich::class, ['id' => 'fachbereich_id']);
    }

    /**
     * {@inheritdoc}
     * @return ProduktQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProduktQuery(get_called_class());
    }

    public function buche($delta, $benutzerId)
    {
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $neuerBestand = $this->quantitaet + $delta;

            $bewegung = new BestandBewegung();
            $bewegung->produkt_id = $this->id;
            $bewegung->benutzer_id = $benutzerId;
            $bewegung->delta = $delta;
            $bewegung->bestand_nach = $neuerBestand;

            if(!$bewegung->save()) {
                $transaction->rollBack();
                return false;
            }

            $this->quantitaet = $neuerBestand;
            if(!$this->save()) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            return false;
        }
    }

}
