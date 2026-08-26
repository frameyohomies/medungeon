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
<head>
    <?php $this->registerJs('window.istAdmin = ' . (Yii::$app->user->identity->rolle === 'admin' ? 'true' : 'false') . ';', \yii\web\View::POS_HEAD); ?>
</head>
<div class="produkt-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity->rolle === 'admin'): ?>
        <p><?= Html::a('Create Produkt', ['create'], ['class' => 'btn btn-success']) ?></p>
    <?php endif; ?>

    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#scanModal">
        Scannen
    </button>

    <div class="row g-3">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div
                    class="card h-100 produkt-card <?= $model->quantitaet <= $model->mindestbestand ? 'produkt-card--warn' : '' ?>"
                    style="--fach-color: <?= Html::encode($model->fachbereich->farbe ?? '#8ea699') ?>;"
                >
                    <div class="produkt-card__accent"></div>
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="produkt-card__title mb-0"><?= Html::encode($model->name) ?></h5>
                            <?php if ($model->fachbereich): ?>
                                <span class="produkt-card__fach-badge">
                                    <?= Html::encode($model->fachbereich->bezeichnung) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($model->quantitaet <= $model->mindestbestand): ?>
                            <span class="produkt-card__badge mb-2 d-inline-block">Nachbestellen</span>
                        <?php endif; ?>

                        <div class="produkt-card__row">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" class="produkt-card__icon">
                                <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/>
                            </svg>
                            <span><?= Html::encode($model->standort) ?></span>
                        </div>

                        <div class="produkt-card__row produkt-card__row--quantity">
                            <span class="produkt-card__number <?= $model->quantitaet <= $model->mindestbestand ? 'text-danger' : '' ?>">
                                <?= $model->quantitaet ?>
                            </span>
                            <span class="text-muted">/ Mindest: <?= $model->mindestbestand ?></span>
                        </div>

                    </div>
                    <div class="card-footer produkt-card__footer">
                        <?= Html::a(
                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
</svg>',
                            ['view', 'id' => $model->id],
                            ['class' => 'btn btn-sm btn-outline-info', 'encode' => false]
                        ) ?>
                        <?php if (Yii::$app->user->identity->rolle === 'admin'): ?>
                            <?= Html::a(
                                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
</svg>',
                                ['update', 'id' => $model->id],
                                ['class' => 'btn btn-sm btn-outline-warning', 'encode' => false]
                            ) ?>
                            <?= Html::a(
                                '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
</svg>',
                                ['delete', 'id' => $model->id],
                                [
                                    'class' => 'btn btn-sm btn-outline-danger',
                                    'encode' => false,
                                    'data' => ['confirm' => 'Wirklich löschen?', 'method' => 'post'],
                                ]
                            ) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?= yii\widgets\LinkPager::widget([
        'pagination' => $dataProvider->getPagination(),
    ]) ?>

    <div class="modal fade" id="scanModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Barcode scannen</h5>
                    <div class="d-flex align-items-center gap-2 ms-auto">
                        <button type="button" class="produkt-card__search-btn" onclick="startScan()" title="Kamera-Scan">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M15 12a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h1.172a3 3 0 0 0 2.12-.879l.83-.828A1 1 0 0 1 6.827 3h2.344a1 1 0 0 1 .707.293l.828.828A3 3 0 0 0 12.828 5H14a1 1 0 0 1 1 1zM2 4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2h-1.172a2 2 0 0 1-1.414-.586l-.828-.828A2 2 0 0 0 9.172 2H6.828a2 2 0 0 0-1.414.586l-.828.828A2 2 0 0 1 3.172 4z"/>
                                <path d="M8 11a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5m0 1a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7M3 6.5a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                            </svg>
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="scanStep">
                        <div id="reader" style="width: 100%;"></div>
                        <input type="text" id="barcodeInput" class="form-control" placeholder="Barcode scannen oder eingeben" autofocus>
                        <button type="button" id="searchButton" class="produkt-card__search-full-btn mt-2" onclick="barcodeLookup(document.getElementById('barcodeInput').value)">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                            </svg>
                            Suchen
                        </button>
                    </div>

                    <div id="produktStep" style="display:none;">
                        <h4 id="produktName"></h4>
                        <p>Aktueller Bestand: <span id="produktQuantitaet"></span></p>

                        <div class="d-flex align-items-center gap-2 mb-3">
                            <button type="button" class="btn btn-outline-secondary" onclick="mengeAnpassen(-1)">−</button>
                            <input type="number" id="deltaInput" class="form-control text-center" value="0">
                            <button type="button" class="btn btn-outline-secondary" onclick="mengeAnpassen(1)">+</button>
                        </div>

                        <button type="button" class="btn btn-primary mb-3 w-100" onclick="buchungAbsenden()">Buchen</button>

                        <div id="produktCrudButtons" class="d-flex gap-2"></div>
                    </div>

                    <div id="fehlerStep" style="display:none;">
                        <p class="text-danger">Barcode nicht gefunden.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
