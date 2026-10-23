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

    /** Admins only: also show deactivated products */
    public $inaktiv;

    public function rules()
    {
        return [
            [['id', 'quantitaet', 'mindestbestand'], 'integer'],
            [['name', 'barcode', 'standort', 'erstellt_am', 'aktualisiert_am'], 'safe'],
            [['q'], 'string', 'max' => 150],
            [['inaktiv'], 'boolean'],
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
            $query->where('0=1');
            return $dataProvider;
        }

        $istAdmin = (bool) \Yii::$app->user->identity?->isRolleAdmin();
        if (!($istAdmin && $this->inaktiv)) {
            $query->andWhere(['produkt.aktiv' => 1]);
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
}
