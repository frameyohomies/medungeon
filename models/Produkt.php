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

    /**
     * {@inheritdoc}
     * @return ProduktQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ProduktQuery(get_called_class());
    }

    /**
     * Books a stock movement and writes the history entry in one transaction.
     *
     * The product row is locked (SELECT ... FOR UPDATE) so concurrent bookings
     * are serialized and always calculate from the current stock.
     * On failure the reason is available via getFirstError('quantitaet').
     */
    public function buche(int $delta, int $benutzerId): bool
    {
        if ($delta === 0) {
            $this->addError('quantitaet', 'Die Menge darf nicht 0 sein.');
            return false;
        }

        $db = static::getDb();
        $transaction = $db->beginTransaction();
        try {
            $aktuell = $db->createCommand(
                'SELECT [[quantitaet]] FROM {{%produkt}} WHERE [[id]] = :id FOR UPDATE',
                [':id' => $this->id]
            )->queryScalar();

            if ($aktuell === false) {
                $transaction->rollBack();
                $this->addError('quantitaet', 'Produkt nicht gefunden.');
                return false;
            }

            $aktuell = (int) $aktuell;
            $this->quantitaet = $aktuell;
            $neuerBestand = $aktuell + $delta;

            if ($neuerBestand < 0) {
                $transaction->rollBack();
                $this->addError('quantitaet', "Nicht genug Bestand (aktuell {$aktuell}).");
                return false;
            }

            $bewegung = new BestandBewegung();
            $bewegung->produkt_id = $this->id;
            $bewegung->benutzer_id = $benutzerId;
            $bewegung->delta = $delta;
            $bewegung->bestand_nach = $neuerBestand;

            if (!$bewegung->save()) {
                $transaction->rollBack();
                $this->addError('quantitaet', 'Buchung konnte nicht gespeichert werden.');
                return false;
            }

            $this->updateAttributes(['quantitaet' => $neuerBestand]);

            $transaction->commit();
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error($e, __METHOD__);
            $this->addError('quantitaet', 'Buchung fehlgeschlagen.');
            return false;
        }
    }
}
