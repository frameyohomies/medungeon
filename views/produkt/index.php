<?php

use app\models\Produkt;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\models\ProduktSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Produkts');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="produkt-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Produkt'), ['create'], ['class' => 'btn btn-success']) ?>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#scanModal">
            Scannen
        </button>
    </p>

    <?php Pjax::begin(); ?>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'barcode',
            'standort',
            'quantitaet',
            //'mindestbestand',
            //'erstellt_am',
            //'aktualisiert_am',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Produkt $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                },
            ],
        ],
    ]);

    ?>

    <?php Pjax::end(); ?>

    <div class="modal fade" id="scanModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Barcode scannen</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    <button type="button" onclick="startScan()">Kamera</button>
                </div>
                <div class="modal-body">
                    <div id="scanStep">
                        <div id="reader" style="width: 100%;"></div>
                        <input type="text" id="barcodeInput" class="form-control"
                               placeholder="Barcode scannen oder eingeben" autofocus>
                    </div>

                    <div id="produktStep" style="display:none;">
                        <h4 id="produktName"></h4>
                        <p>Aktueller Bestand: <span id="produktQuantitaet"></span></p>

                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-secondary" onclick="mengeAnpassen(-1)">−
                            </button>
                            <input type="number" id="deltaInput" class="form-control text-center" value="0">
                            <button type="button" class="btn btn-outline-secondary" onclick="mengeAnpassen(1)">+
                            </button>
                        </div>

                        <button type="button" class="btn btn-primary mt-3 w-100" onclick="buchungAbsenden()">Buchen
                        </button>
                    </div>

                    <div id="fehlerStep" style="display:none;">
                        <p class="text-danger">Barcode nicht gefunden.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
