<?php

use app\models\BestandBewegung;
use yii\bootstrap5\ActiveForm;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\BestandBewegungSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Verlauf';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bestand-bewegung-index">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0"><?= Html::encode($this->title) ?></h1>
        <?= Html::a(
            'CSV exportieren',
            array_merge(['export'], Yii::$app->request->queryParams),
            ['class' => 'btn btn-outline-secondary']
        ) ?>
    </div>

    <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
    <div class="row g-2 mb-3 align-items-end">
        <div class="col-md-3"><?= $form->field($searchModel, 'produktName')->textInput(['placeholder' => 'Name oder Barcode']) ?></div>
        <div class="col-md-3"><?= $form->field($searchModel, 'benutzerName')->textInput(['placeholder' => 'Name oder E-Mail']) ?></div>
        <div class="col-md-2"><?= $form->field($searchModel, 'von')->input('date') ?></div>
        <div class="col-md-2"><?= $form->field($searchModel, 'bis')->input('date') ?></div>
        <div class="col-md-2 d-flex gap-2 mb-3">
            <?= Html::submitButton('Filtern', ['class' => 'btn btn-primary flex-fill']) ?>
            <?= Html::a('Zurücksetzen', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    <?php ActiveForm::end(); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'gebucht_am',
                'label' => 'Zeitpunkt',
                'format' => ['datetime', 'php:d.m.Y H:i'],
            ],
            [
                'attribute' => 'produktName',
                'label' => 'Produkt',
                'value' => fn (BestandBewegung $m) => $m->produkt->name ?? ('#' . $m->produkt_id),
            ],
            [
                'attribute' => 'delta',
                'label' => 'Menge',
                'format' => 'raw',
                'value' => fn (BestandBewegung $m) => Html::tag(
                    'span',
                    ($m->delta > 0 ? '+' : '') . $m->delta,
                    ['class' => $m->delta > 0 ? 'text-success fw-semibold' : 'text-danger fw-semibold']
                ),
            ],
            [
                'attribute' => 'bestand_nach',
                'label' => 'Bestand danach',
            ],
            [
                'attribute' => 'benutzerName',
                'label' => 'Benutzer',
                'value' => fn (BestandBewegung $m) => $m->benutzer
                    ? trim($m->benutzer->firstname . ' ' . $m->benutzer->lastname)
                    : ('#' . $m->benutzer_id),
            ],
            [
                'class' => ActionColumn::class,
                'template' => '{view}',
                'urlCreator' => fn ($action, BestandBewegung $model) => Url::toRoute([$action, 'id' => $model->id]),
            ],
        ],
    ]); ?>

</div>
