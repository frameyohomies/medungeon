<?php

use app\models\Benutzer;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\models\BenutzerSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Benutzer');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="benutzer-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'entra_oid',
            'firstname',
            'lastname',
            'email:email',
            'rolle',
            [
                'attribute' => 'aktiv',
                'format' => 'raw',
                'filter' => [1 => 'Aktiv', 0 => 'Deaktiviert'],
                'value' => fn (Benutzer $m) => $m->aktiv
                    ? '<span class="badge bg-success">Aktiv</span>'
                    : '<span class="badge bg-secondary">Deaktiviert</span>',
            ],
            [
                'class' => ActionColumn::className(),
                'template' => '{view} {update}',
                'urlCreator' => function ($action, Benutzer $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>

    <?php Pjax::end(); ?>

</div>
