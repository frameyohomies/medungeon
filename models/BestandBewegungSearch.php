<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Search/filter model for the stock history (Verlauf).
 */
class BestandBewegungSearch extends BestandBewegung
{
    /** Free-text filter on product name or barcode */
    public $produktName;

    /** Free-text filter on first/last name or email of the booking user */
    public $benutzerName;

    /** Date range (Y-m-d, inclusive) */
    public $von;
    public $bis;

    public function rules()
    {
        return [
            [['produkt_id', 'benutzer_id'], 'integer'],
            [['produktName', 'benutzerName'], 'string', 'max' => 150],
            [['von', 'bis'], 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'produktName' => 'Produkt',
            'benutzerName' => 'Benutzer',
            'von' => 'Von',
            'bis' => 'Bis',
        ];
    }

    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @param string|null $formName
     * @param bool $paginate false for exports
     */
    public function search($params, $formName = null, bool $paginate = true): ActiveDataProvider
    {
        $query = BestandBewegung::find()
            ->alias('bb')
            // join for filtering/sorting only; relations are eager-loaded separately
            ->joinWith(['produkt p', 'benutzer b'], false)
            ->with(['produkt', 'benutzer']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => $paginate ? ['pageSize' => 50] : false,
            'sort' => [
                'defaultOrder' => ['gebucht_am' => SORT_DESC],
                'attributes' => [
                    'gebucht_am' => ['asc' => ['bb.gebucht_am' => SORT_ASC, 'bb.id' => SORT_ASC], 'desc' => ['bb.gebucht_am' => SORT_DESC, 'bb.id' => SORT_DESC]],
                    'delta' => ['asc' => ['bb.delta' => SORT_ASC], 'desc' => ['bb.delta' => SORT_DESC]],
                    'produktName' => ['asc' => ['p.name' => SORT_ASC], 'desc' => ['p.name' => SORT_DESC]],
                    'benutzerName' => ['asc' => ['b.lastname' => SORT_ASC, 'b.firstname' => SORT_ASC], 'desc' => ['b.lastname' => SORT_DESC, 'b.firstname' => SORT_DESC]],
                ],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            $query->where('0=1');
            return $dataProvider;
        }

        $query->andFilterWhere(['bb.produkt_id' => $this->produkt_id, 'bb.benutzer_id' => $this->benutzer_id]);

        if ($this->produktName !== null && $this->produktName !== '') {
            $query->andWhere(['or', ['like', 'p.name', $this->produktName], ['like', 'p.barcode', $this->produktName]]);
        }

        if ($this->benutzerName !== null && $this->benutzerName !== '') {
            $query->andWhere(['or',
                ['like', 'b.firstname', $this->benutzerName],
                ['like', 'b.lastname', $this->benutzerName],
                ['like', 'b.email', $this->benutzerName],
            ]);
        }

        if ($this->von) {
            $query->andWhere(['>=', 'bb.gebucht_am', $this->von . ' 00:00:00']);
        }
        if ($this->bis) {
            $query->andWhere(['<=', 'bb.gebucht_am', $this->bis . ' 23:59:59']);
        }

        return $dataProvider;
    }
}
