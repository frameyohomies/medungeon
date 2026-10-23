<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\BestandBewegung $model */

$this->title = 'Buchung #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Verlauf', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$vorher = $model->bestand_nach - $model->delta;
$ts = strtotime($model->gebucht_am);
?>
<div class="bestand-bewegung-view">

    <div class="surface receipt mx-auto">
        <div class="receipt__top">
            <span class="delta <?= $model->delta > 0 ? 'delta--in' : 'delta--out' ?>">
                <?= $model->delta > 0 ? '+' . $model->delta : '−' . abs($model->delta) ?>
            </span>
            <div class="receipt__product">
                <?php if ($model->produkt): ?>
                    <?= Html::a(Html::encode($model->produkt->name), ['/produkt/view', 'id' => $model->produkt_id]) ?>
                <?php else: ?>
                    #<?= (int) $model->produkt_id ?>
                <?php endif; ?>
            </div>
            <div class="small" style="color: #a9b3ad;">
                <?= $model->delta > 0 ? 'Zugang' : 'Entnahme' ?> · <?= date('d.m.Y', $ts) ?> um <?= date('H:i:s', $ts) ?>
            </div>
        </div>

        <dl class="info-list">
            <div class="info-list__row">
                <dt class="info-list__label">Bestand</dt>
                <dd class="info-list__value mb-0"><?= (int) $vorher ?> &rarr; <strong><?= (int) $model->bestand_nach ?></strong></dd>
            </div>
            <?php if ($model->produkt && $model->produkt->barcode): ?>
                <div class="info-list__row">
                    <dt class="info-list__label">Barcode</dt>
                    <dd class="info-list__value mb-0"><code><?= Html::encode($model->produkt->barcode) ?></code></dd>
                </div>
            <?php endif; ?>
            <?php if ($model->produkt && $model->produkt->standort): ?>
                <div class="info-list__row">
                    <dt class="info-list__label">Standort</dt>
                    <dd class="info-list__value mb-0"><?= Html::encode($model->produkt->standort) ?></dd>
                </div>
            <?php endif; ?>
            <div class="info-list__row">
                <dt class="info-list__label">Gebucht von</dt>
                <dd class="info-list__value mb-0">
                    <?php if ($model->benutzer): ?>
                        <?= Html::a(
                            Html::encode(trim($model->benutzer->firstname . ' ' . $model->benutzer->lastname)),
                            ['/benutzer/view', 'id' => $model->benutzer_id],
                            ['style' => 'color: var(--color-primary-lighter);']
                        ) ?>
                        <div class="small fw-normal" style="color: #a9b3ad;"><?= Html::encode($model->benutzer->email) ?></div>
                    <?php else: ?>
                        #<?= (int) $model->benutzer_id ?>
                    <?php endif; ?>
                </dd>
            </div>
            <div class="info-list__row">
                <dt class="info-list__label">Buchungs-ID</dt>
                <dd class="info-list__value mb-0">#<?= (int) $model->id ?></dd>
            </div>
        </dl>
    </div>

    <p class="text-center mt-3 mb-0">
        <?= Html::a('&lsaquo; Zurück zum Verlauf', ['index'], ['class' => 'small']) ?>
        <?php if ($model->produkt): ?>
            · <?= Html::a(
                'Alle Buchungen dieses Produkts',
                ['index', 'BestandBewegungSearch' => ['produkt_id' => $model->produkt_id]],
                ['class' => 'small']
            ) ?>
        <?php endif; ?>
    </p>

</div>
