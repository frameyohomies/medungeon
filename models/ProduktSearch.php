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
    public function rules()
    {
        return [
            [['id', 'quantitaet', 'mindestbestand'], 'integer'],
            [['name', 'barcode', 'standort', 'erstellt_am', 'aktualisiert_am'], 'safe'],
        ];
    }

    public $q;
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
        $query = Produkt::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'quantitaet' => $this->quantitaet,
            'mindestbestand' => $this->mindestbestand,
            'erstellt_am' => $this->erstellt_am,
            'aktualisiert_am' => $this->aktualisiert_am,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'barcode', $this->barcode])
            ->andFilterWhere(['like', 'standort', $this->standort]);

        return $dataProvider;

        $query = Produkt::find()->joinWith('fachbereich');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if (!empty($this->q)) {
            $query->andFilterWhere(['or',
                ['like', 'produkt.name', $this->q],
                ['like', 'produkt.barcode', $this->q],
                ['like', 'produkt.standort', $this->q],
                ['like', 'fachbereich.bezeichnung', $this->q],
            ]);
        }

        return $dataProvider;
    }
}
