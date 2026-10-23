<?php

namespace app\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Search model for the user list: free-text search + status segment.
 */
class BenutzerSearch extends Benutzer
{
    public const FILTER_ALLE = 'alle';
    public const FILTER_ADMINS = 'admins';
    public const FILTER_DEAKTIVIERT = 'deaktiviert';

    /** Free-text search over first name, last name and email */
    public $q;

    /** One of the FILTER_* constants */
    public $filter = self::FILTER_ALLE;

    public function rules()
    {
        return [
            [['q'], 'string', 'max' => 150],
            [['filter'], 'in', 'range' => [self::FILTER_ALLE, self::FILTER_ADMINS, self::FILTER_DEAKTIVIERT]],
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
     */
    public function search($params, $formName = null): ActiveDataProvider
    {
        $query = Benutzer::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 30],
            'sort' => [
                'defaultOrder' => ['aktiv' => SORT_DESC, 'lastname' => SORT_ASC, 'firstname' => SORT_ASC],
            ],
        ]);

        $this->load($params, $formName);

        if (!$this->validate()) {
            $this->filter = self::FILTER_ALLE;
            $query->where('0=1');
            return $dataProvider;
        }

        if ($this->filter === self::FILTER_ADMINS) {
            $query->andWhere(['rolle' => self::ROLLE_ADMIN, 'aktiv' => 1]);
        } elseif ($this->filter === self::FILTER_DEAKTIVIERT) {
            $query->andWhere(['aktiv' => 0]);
        }

        if ($this->q !== null && $this->q !== '') {
            $query->andWhere(['or',
                ['like', 'firstname', $this->q],
                ['like', 'lastname', $this->q],
                ['like', 'email', $this->q],
                ['like', "CONCAT(firstname, ' ', lastname)", $this->q],
            ]);
        }

        return $dataProvider;
    }

    /**
     * Counts for the summary chips (independent of the current filter).
     *
     * @return array{gesamt:int, aktiv:int, admins:int, deaktiviert:int}
     */
    public static function zaehler(): array
    {
        $rows = Benutzer::find()
            ->select(['aktiv', 'rolle', 'n' => 'COUNT(*)'])
            ->groupBy(['aktiv', 'rolle'])
            ->asArray()
            ->all();

        $z = ['gesamt' => 0, 'aktiv' => 0, 'admins' => 0, 'deaktiviert' => 0];
        foreach ($rows as $r) {
            $n = (int) $r['n'];
            $z['gesamt'] += $n;
            if ((int) $r['aktiv'] === 1) {
                $z['aktiv'] += $n;
                if ($r['rolle'] === self::ROLLE_ADMIN) {
                    $z['admins'] += $n;
                }
            } else {
                $z['deaktiviert'] += $n;
            }
        }

        return $z;
    }
}
