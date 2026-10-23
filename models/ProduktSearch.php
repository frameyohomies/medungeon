<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use app\models\Produkt;

/**
 * ProduktSearch represents the model behind the search form of `app\models\Produkt`.
 */
class ProduktSearch extends Produkt
{
    /**
     * {@inheritdoc}
     */
    /** Free-text search over name, barcode, location and department */
    public $q;

    public const FILTER_ALLE = 'alle';
    public const FILTER_KNAPP = 'knapp';
    public const FILTER_INAKTIV = 'inaktiv';

    /** alle = active products, knapp = at/below minimum stock, inaktiv = deactivated (admins only) */
    public $filter = self::FILTER_ALLE;

    public function rules()
    {
        return [
            [['id', 'quantitaet', 'mindestbestand'], 'integer'],
            [['name', 'barcode', 'standort', 'erstellt_am', 'aktualisiert_am'], 'safe'],
            [['q'], 'string', 'max' => 150],
            [['filter'], 'in', 'range' => [self::FILTER_ALLE, self::FILTER_KNAPP, self::FILTER_INAKTIV]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     * @param string|null $formName Form name to be used into `->load()` method.
     *
     * @return ActiveDataProvider
     */
    public function search($params, $formName = null)
    {
        $query = Produkt::find()->alias('produkt')->joinWith('fachbereich');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['name' => SORT_ASC],
                'attributes' => ['name' => ['asc' => ['produkt.name' => SORT_ASC], 'desc' => ['produkt.name' => SORT_DESC]]],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            $this->filter = self::FILTER_ALLE;
            $query->where('0=1');
            return $dataProvider;
        }

        $istAdmin = (bool) \Yii::$app->user->identity?->isRolleAdmin();
        if ($this->filter === self::FILTER_INAKTIV && $istAdmin) {
            $query->andWhere(['produkt.aktiv' => 0]);
        } else {
            $query->andWhere(['produkt.aktiv' => 1]);
            if ($this->filter === self::FILTER_KNAPP) {
                $query->andWhere('produkt.quantitaet <= produkt.mindestbestand');
            }
        }

        $query->andFilterWhere([
            'produkt.id' => $this->id,
            'produkt.quantitaet' => $this->quantitaet,
            'produkt.mindestbestand' => $this->mindestbestand,
        ]);

        $query->andFilterWhere(['like', 'produkt.name', $this->name])
            ->andFilterWhere(['like', 'produkt.barcode', $this->barcode])
            ->andFilterWhere(['like', 'produkt.standort', $this->standort]);

        if ($this->q !== null && $this->q !== '') {
            $query->andWhere(['or',
                ['like', 'produkt.name', $this->q],
                ['like', 'produkt.barcode', $this->q],
                ['like', 'produkt.standort', $this->q],
                ['like', 'fachbereich.bezeichnung', $this->q],
            ]);
        }

        return $dataProvider;
    }

    /**
     * Counts for the page header (active products only).
     *
     * @return array{aktiv:int, knapp:int, inaktiv:int}
     */
    public static function zaehler(): array
    {
        $row = Produkt::find()
            ->select([
                'aktiv' => 'COALESCE(SUM(aktiv = 1), 0)',
                'knapp' => 'COALESCE(SUM(aktiv = 1 AND quantitaet <= mindestbestand), 0)',
                'inaktiv' => 'COALESCE(SUM(aktiv = 0), 0)',
            ])
            ->asArray()
            ->one();

        return array_map('intval', $row ?: ['aktiv' => 0, 'knapp' => 0, 'inaktiv' => 0]);
    }
}
