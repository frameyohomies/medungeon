<?php

use app\models\Benutzer;
use app\models\Produkt;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var app\models\BestandBewegungSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Verlauf';
$this->params['breadcrumbs'][] = $this->title;

// Totals over the whole filtered result (not only the current page)
$summenQuery = clone $dataProvider->query;
$summenQuery->with = [];
$summe = $summenQuery
    ->orderBy(null)
    ->select([
        'anzahl' => 'COUNT(*)',
        'zugang' => 'COALESCE(SUM(CASE WHEN bb.delta > 0 THEN bb.delta ELSE 0 END), 0)',
        'abgang' => 'COALESCE(SUM(CASE WHEN bb.delta < 0 THEN -bb.delta ELSE 0 END), 0)',
    ])
    ->asArray()
    ->one();

// Active "hidden" filters coming from links (e.g. from a user profile)
$fixFilter = [];
if ($searchModel->benutzer_id && ($u = Benutzer::findOne($searchModel->benutzer_id))) {
    $fixFilter[] = 'Benutzer: ' . trim($u->firstname . ' ' . $u->lastname);
}
if ($searchModel->produkt_id && ($p = Produkt::findOne($searchModel->produkt_id))) {
    $fixFilter[] = 'Produkt: ' . $p->name;
}

$hatFilter = $fixFilter || $searchModel->produktName || $searchModel->benutzerName || $searchModel->von || $searchModel->bis;

$wochentage = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
$heute = date('Y-m-d');
$gestern = date('Y-m-d', strtotime('-1 day'));
$tagLabel = static function (string $tag) use ($heute, $gestern, $wochentage): string {
    if ($tag === $heute) {
        return 'Heute';
    }
    if ($tag === $gestern) {
        return 'Gestern';
    }
    $ts = strtotime($tag);
    return $wochentage[(int) date('w', $ts)] . ', ' . date('d.m.Y', $ts);
};

// group current page by day (list is sorted newest first)
$tage = [];
foreach ($dataProvider->getModels() as $m) {
    $tage[substr((string) $m->gebucht_am, 0, 10)][] = $m;
}
?>
<div class="bestand-bewegung-index">

    <div class="page-head">
        <div>
            <h1 class="page-head__title"><?= Html::encode($this->title) ?></h1>
            <p class="page-head__sub">Jede Buchung nachvollziehbar – wer, wann, wie viel.</p>
        </div>
        <?= Html::a(
            '<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" viewBox="0 0 16 16" class="me-1" aria-hidden="true"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/></svg>CSV exportieren',
            array_merge(['export'], Yii::$app->request->queryParams),
            ['class' => 'btn btn-outline-primary', 'encode' => false]
        ) ?>
    </div>

    <div class="stat-row">
        <div class="surface stat-chip">
            <div class="stat-chip__value"><?= (int) $summe['anzahl'] ?></div>
            <div class="stat-chip__label">Buchungen<?= $hatFilter ? ' (gefiltert)' : '' ?></div>
        </div>
        <div class="surface stat-chip" style="--chip-color: #9cc4ad;">
            <div class="stat-chip__value">+<?= (int) $summe['zugang'] ?></div>
            <div class="stat-chip__label">Zugänge</div>
        </div>
        <div class="surface stat-chip" style="--chip-color: #e09a92;">
            <div class="stat-chip__value">−<?= (int) $summe['abgang'] ?></div>
            <div class="stat-chip__label">Entnahmen</div>
        </div>
    </div>

    <div class="surface filter-bar">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
        <?= Html::activeHiddenInput($searchModel, 'benutzer_id') ?>
        <?= Html::activeHiddenInput($searchModel, 'produkt_id') ?>
        <div class="row g-2 align-items-end">
            <div class="col-sm-6 col-lg-3"><?= $form->field($searchModel, 'produktName')->textInput(['placeholder' => 'Name oder Barcode']) ?></div>
            <div class="col-sm-6 col-lg-3"><?= $form->field($searchModel, 'benutzerName')->textInput(['placeholder' => 'Name oder E-Mail']) ?></div>
            <div class="col-6 col-lg-2"><?= $form->field($searchModel, 'von')->input('date') ?></div>
            <div class="col-6 col-lg-2"><?= $form->field($searchModel, 'bis')->input('date') ?></div>
            <div class="col-lg-2 d-flex gap-2">
                <?= Html::submitButton('Filtern', ['class' => 'btn btn-primary flex-fill']) ?>
                <?php if ($hatFilter): ?>
                    <?= Html::a('&times;', ['index'], ['class' => 'btn btn-ghost-light', 'title' => 'Filter zurücksetzen', 'aria-label' => 'Filter zurücksetzen']) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php if ($fixFilter): ?>
            <div class="seg mt-2">
                <?php foreach ($fixFilter as $f): ?>
                    <span class="seg__item seg__item--active"><?= Html::encode($f) ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php ActiveForm::end(); ?>
    </div>

    <?php if (!$tage): ?>
        <div class="surface empty-state">
            <span class="empty-state__icon" aria-hidden="true">&#128340;</span>
            <?= $hatFilter ? 'Keine Buchungen für diese Filter.' : 'Noch keine Buchungen.' ?>
        </div>
    <?php else: ?>
        <?php foreach ($tage as $tag => $eintraege): ?>
            <section class="log-day">
                <div class="log-day__head">
                    <span class="log-day__label"><?= Html::encode($tagLabel($tag)) ?></span>
                    <span class="log-day__count"><?= count($eintraege) ?> <?= count($eintraege) === 1 ? 'Buchung' : 'Buchungen' ?></span>
                </div>
                <div class="surface log-list">
                    <?php foreach ($eintraege as $b): ?>
                        <?php
                        $wer = $b->benutzer ? trim($b->benutzer->firstname . ' ' . $b->benutzer->lastname) : ('#' . $b->benutzer_id);
                        $was = $b->produkt->name ?? ('#' . $b->produkt_id);
                        ?>
                        <a class="log-entry" href="<?= Html::encode(Url::to(['view', 'id' => $b->id])) ?>">
                            <span class="delta <?= $b->delta > 0 ? 'delta--in' : 'delta--out' ?>">
                                <?= $b->delta > 0 ? '+' . $b->delta : '−' . abs($b->delta) ?>
                            </span>
                            <div style="min-width: 0;">
                                <div class="log-entry__product"><?= Html::encode($was) ?></div>
                                <div class="log-entry__sub"><?= Html::encode($wer) ?></div>
                            </div>
                            <div class="log-entry__right">
                                <div class="log-entry__time"><?= date('H:i', strtotime($b->gebucht_am)) ?></div>
                                <div class="log-entry__after">Bestand <?= (int) $b->bestand_nach ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <div class="pager-wrap">
            <?= LinkPager::widget(['pagination' => $dataProvider->getPagination()]) ?>
        </div>
    <?php endif; ?>

</div>
