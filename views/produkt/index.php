<?php

use app\models\ProduktSearch;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\ProduktSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Bestand';
$this->params['breadcrumbs'][] = $this->title;

$istAdmin = (bool) Yii::$app->user->identity?->isRolleAdmin();
$this->registerJs('window.istAdmin = ' . ($istAdmin ? 'true' : 'false') . ';', \yii\web\View::POS_HEAD);

$z = ProduktSearch::zaehler();
$filter = $searchModel->filter ?: ProduktSearch::FILTER_ALLE;
$segmente = [ProduktSearch::FILTER_ALLE => 'Alle', ProduktSearch::FILTER_KNAPP => 'Nachbestellen'];
if ($istAdmin) {
    $segmente[ProduktSearch::FILTER_INAKTIV] = 'Deaktiviert';
}
$zahlen = [ProduktSearch::FILTER_ALLE => $z['aktiv'], ProduktSearch::FILTER_KNAPP => $z['knapp'], ProduktSearch::FILTER_INAKTIV => $z['inaktiv']];

$iconScan = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M1.5 1a.5.5 0 0 0-.5.5v3a.5.5 0 0 1-1 0v-3A1.5 1.5 0 0 1 1.5 0h3a.5.5 0 0 1 0 1zM11 .5a.5.5 0 0 1 .5-.5h3A1.5 1.5 0 0 1 16 1.5v3a.5.5 0 0 1-1 0v-3a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 1-.5-.5M.5 11a.5.5 0 0 1 .5.5v3a.5.5 0 0 0 .5.5h3a.5.5 0 0 1 0 1h-3A1.5 1.5 0 0 1 0 14.5v-3a.5.5 0 0 1 .5-.5m15 0a.5.5 0 0 1 .5.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a.5.5 0 0 1 0-1h3a.5.5 0 0 0 .5-.5v-3a.5.5 0 0 1 .5-.5M3 4.5a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0zm2 0a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v7a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5zm3 0a.5.5 0 0 1 1 0v7a.5.5 0 0 1-1 0z"/></svg>';
$iconSearch = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/></svg>';
$iconPencil = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/></svg>';
$iconArchive = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M0 2a1 1 0 0 1 1-1h14a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1v7.5a2.5 2.5 0 0 1-2.5 2.5h-9A2.5 2.5 0 0 1 1 12.5V5a1 1 0 0 1-1-1zm2 3v7.5A1.5 1.5 0 0 0 3.5 14h9a1.5 1.5 0 0 0 1.5-1.5V5zm13-3H1v2h14zM5 7.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/></svg>';
$iconPlusMinus = '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 2a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 2M4.5 13a.5.5 0 0 0 0 1h7a.5.5 0 0 0 0-1z"/></svg>';
?>
<div class="produkt-index">

    <div class="page-head">
        <div>
            <h1 class="page-head__title"><?= Html::encode($this->title) ?></h1>
            <p class="page-head__sub">
                <?= $z['aktiv'] ?> Produkte
                <?php if ($z['knapp'] > 0): ?>
                    · <?= Html::a(
                        $z['knapp'] . ' unter Mindestbestand',
                        ['index', 'ProduktSearch' => ['filter' => ProduktSearch::FILTER_KNAPP]],
                        ['class' => 'text-danger fw-semibold text-decoration-none']
                    ) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="page-head__actions">
            <?php if ($istAdmin): ?>
                <?= Html::a('+ Neues Produkt', ['create'], ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
            <button type="button" class="btn btn-primary btn-scan d-none d-md-inline-flex" data-bs-toggle="modal" data-bs-target="#scanModal">
                <?= $iconScan ?> Scannen
            </button>
        </div>
    </div>

    <div class="surface filter-bar">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
        <?= Html::activeHiddenInput($searchModel, 'filter') ?>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="search-field flex-grow-1">
                <span class="search-field__icon"><?= $iconSearch ?></span>
                <?= Html::activeTextInput($searchModel, 'q', [
                    'class' => 'form-control',
                    'placeholder' => 'Name, Barcode, Standort oder Fachbereich…',
                    'aria-label' => 'Produkte suchen',
                ]) ?>
            </div>
            <?= Html::submitButton('Suchen', ['class' => 'btn btn-primary']) ?>
            <div class="seg ms-md-2">
                <?php foreach ($segmente as $key => $label): ?>
                    <?= Html::a(
                        Html::encode($label) . ' <span class="seg__count">' . (int) $zahlen[$key] . '</span>',
                        ['index', 'ProduktSearch' => ['filter' => $key, 'q' => $searchModel->q]],
                        ['class' => 'seg__item' . ($filter === $key ? ' seg__item--active' : '') . ($key === ProduktSearch::FILTER_KNAPP && $zahlen[$key] > 0 ? ' seg__item--warn' : '')]
                    ) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if (!$dataProvider->getModels()): ?>
        <div class="surface empty-state">
            <span class="empty-state__icon" aria-hidden="true">&#128230;</span>
            <?php if ($searchModel->q): ?>
                Nichts gefunden für „<?= Html::encode($searchModel->q) ?>“.
            <?php elseif ($filter === ProduktSearch::FILTER_KNAPP): ?>
                Alles im grünen Bereich – kein Produkt unter Mindestbestand.
            <?php else: ?>
                Keine Produkte.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div
                    class="card h-100 produkt-card<?= !$model->aktiv ? ' produkt-card--inactive' : ($model->quantitaet <= $model->mindestbestand ? ' produkt-card--warn' : '') ?>"
                    style="--fach-color: <?= Html::encode($model->fachbereich->farbe ?? '#8ea699') ?>;"
                >
                    <div class="produkt-card__accent"></div>
                    <div class="card-body position-relative">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="produkt-card__title mb-0">
                                <?= Html::a(Html::encode($model->name), ['view', 'id' => $model->id], [
                                    'class' => 'produkt-card__link stretched-link',
                                ]) ?>
                            </h5>
                            <?php if ($model->fachbereich): ?>
                                <span class="produkt-card__fach-badge">
                                    <?= Html::encode($model->fachbereich->bezeichnung) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if (!$model->aktiv): ?>
                            <span class="pill pill--inactive mb-2">Deaktiviert</span>
                        <?php elseif ($model->quantitaet <= $model->mindestbestand): ?>
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
                        <?php if ($model->aktiv): ?>
                            <button type="button" class="btn btn-sm produkt-card__book" onclick="buchungOeffnen(<?= (int) $model->id ?>)">
                                <?= $iconPlusMinus ?> Buchen
                            </button>
                        <?php elseif ($istAdmin): ?>
                            <?= Html::a('Aktivieren', ['aktivieren', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-success flex-fill',
                                'data' => ['method' => 'post'],
                            ]) ?>
                        <?php endif; ?>

                        <?php if ($istAdmin): ?>
                            <div class="produkt-card__tools">
                                <?= Html::a($iconPencil, ['update', 'id' => $model->id], [
                                    'class' => 'icon-btn',
                                    'encode' => false,
                                    'title' => 'Bearbeiten',
                                    'aria-label' => 'Bearbeiten',
                                ]) ?>
                                <?php if ($model->aktiv): ?>
                                    <?= Html::a($iconArchive, ['deaktivieren', 'id' => $model->id], [
                                        'class' => 'icon-btn icon-btn--danger',
                                        'encode' => false,
                                        'title' => 'Deaktivieren',
                                        'aria-label' => 'Deaktivieren',
                                        'data' => ['confirm' => 'Produkt deaktivieren? Es verschwindet aus der Liste und kann nicht mehr gebucht werden. Der Verlauf bleibt erhalten.', 'method' => 'post'],
                                    ]) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="pager-wrap">
        <?= yii\widgets\LinkPager::widget(['pagination' => $dataProvider->getPagination()]) ?>
    </div>

    <!-- mobile: primary action within thumb reach -->
    <button type="button" class="scan-fab d-md-none" data-bs-toggle="modal" data-bs-target="#scanModal" aria-label="Barcode scannen">
        <?= $iconScan ?> Scannen
    </button>

    <?= $this->render('_buchungModal') ?>

</div>
