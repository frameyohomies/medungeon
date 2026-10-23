<?php

use app\models\BestandBewegung;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Produkt $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Bestand', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$istAdmin = (bool) Yii::$app->user->identity?->isRolleAdmin();
$this->registerJs('window.istAdmin = ' . ($istAdmin ? 'true' : 'false') . ';', \yii\web\View::POS_HEAD);

$farbe = $model->fachbereich->farbe ?? '#8ea699';
$knapp = $model->aktiv && $model->quantitaet <= $model->mindestbestand;

// fill level relative to 2x minimum stock (minimum sits in the middle of the bar)
$skala = max(1, $model->mindestbestand * 2, $model->quantitaet);
$fuellung = min(100, round($model->quantitaet / $skala * 100));
$markierung = $model->mindestbestand > 0 ? min(100, round($model->mindestbestand / $skala * 100)) : null;

$seit = date('Y-m-d H:i:s', strtotime('-30 days'));
$statistik = BestandBewegung::find()
    ->where(['produkt_id' => $model->id])
    ->andWhere(['>=', 'gebucht_am', $seit])
    ->select([
        'zugang' => 'COALESCE(SUM(CASE WHEN delta > 0 THEN delta ELSE 0 END), 0)',
        'abgang' => 'COALESCE(SUM(CASE WHEN delta < 0 THEN -delta ELSE 0 END), 0)',
    ])
    ->asArray()
    ->one();

$letzte = BestandBewegung::find()
    ->where(['produkt_id' => $model->id])
    ->with('benutzer')
    ->orderBy(['gebucht_am' => SORT_DESC, 'id' => SORT_DESC])
    ->limit(8)
    ->all();
?>
<div class="produkt-view" style="--fach-color: <?= Html::encode($farbe) ?>;">

    <div class="surface product-hero<?= $model->aktiv ? '' : ' product-hero--inactive' ?>">
        <div class="product-hero__main">
            <div class="d-flex flex-wrap gap-2 mb-2">
                <?php if ($model->fachbereich): ?>
                    <span class="produkt-card__fach-badge"><?= Html::encode($model->fachbereich->bezeichnung) ?></span>
                <?php endif; ?>
                <?php if (!$model->aktiv): ?>
                    <span class="pill pill--inactive">Deaktiviert</span>
                <?php elseif ($knapp): ?>
                    <span class="produkt-card__badge">Nachbestellen</span>
                <?php endif; ?>
            </div>

            <h1 class="product-hero__name"><?= Html::encode($model->name) ?></h1>

            <div class="product-hero__facts">
                <?php if ($model->standort): ?>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true"><path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6"/></svg>
                        <?= Html::encode($model->standort) ?>
                    </span>
                <?php endif; ?>
                <?php if ($model->barcode): ?>
                    <span><code><?= Html::encode($model->barcode) ?></code></span>
                <?php else: ?>
                    <span class="text-muted">kein Barcode</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="product-hero__stock">
            <div class="product-hero__number<?= $knapp ? ' is-low' : '' ?>"><?= (int) $model->quantitaet ?></div>
            <div class="product-hero__min">Mindestbestand <?= (int) $model->mindestbestand ?></div>
            <div class="stock-bar" role="img" aria-label="Bestand <?= (int) $model->quantitaet ?>, Mindestbestand <?= (int) $model->mindestbestand ?>">
                <div class="stock-bar__fill<?= $knapp ? ' is-low' : '' ?>" style="width: <?= $fuellung ?>%;"></div>
                <?php if ($markierung !== null): ?>
                    <div class="stock-bar__mark" style="left: <?= $markierung ?>%;"></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="product-hero__actions">
            <?php if ($model->aktiv): ?>
                <button type="button" class="btn produkt-card__book" onclick="buchungOeffnen(<?= (int) $model->id ?>)">
                    &plusmn; Buchen
                </button>
            <?php endif; ?>
            <?php if ($istAdmin): ?>
                <?= Html::a('Bearbeiten', ['update', 'id' => $model->id], ['class' => 'btn btn-ghost-light']) ?>
                <?php if ($model->aktiv): ?>
                    <?= Html::a('Deaktivieren', ['deaktivieren', 'id' => $model->id], [
                        'class' => 'btn btn-outline-danger',
                        'data' => [
                            'confirm' => 'Produkt deaktivieren? Es verschwindet aus der Liste und kann nicht mehr gebucht werden. Der Verlauf bleibt erhalten.',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php else: ?>
                    <?= Html::a('Aktivieren', ['aktivieren', 'id' => $model->id], [
                        'class' => 'btn btn-outline-success',
                        'data' => ['method' => 'post'],
                    ]) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-baseline">
                <h2 class="section-title">Letzte Buchungen</h2>
                <?php if ($istAdmin && $letzte): ?>
                    <?= Html::a(
                        'Alle im Verlauf &rsaquo;',
                        ['/bestand-bewegung/index', 'BestandBewegungSearch' => ['produkt_id' => $model->id]],
                        ['class' => 'small']
                    ) ?>
                <?php endif; ?>
            </div>

            <?php if (!$letzte): ?>
                <div class="surface empty-state">Noch keine Buchungen.</div>
            <?php else: ?>
                <div class="surface log-list">
                    <?php foreach ($letzte as $b): ?>
                        <?php $wer = $b->benutzer ? trim($b->benutzer->firstname . ' ' . $b->benutzer->lastname) : ('#' . $b->benutzer_id); ?>
                        <?= Html::beginTag($istAdmin ? 'a' : 'div', [
                            'class' => 'log-entry',
                            'href' => $istAdmin ? Url::to(['/bestand-bewegung/view', 'id' => $b->id]) : null,
                        ]) ?>
                            <span class="delta <?= $b->delta > 0 ? 'delta--in' : 'delta--out' ?>">
                                <?= $b->delta > 0 ? '+' . $b->delta : '−' . abs($b->delta) ?>
                            </span>
                            <div style="min-width: 0;">
                                <div class="log-entry__product"><?= Html::encode($wer) ?></div>
                                <div class="log-entry__sub"><?= date('d.m.Y', strtotime($b->gebucht_am)) ?></div>
                            </div>
                            <div class="log-entry__right">
                                <div class="log-entry__time"><?= date('H:i', strtotime($b->gebucht_am)) ?></div>
                                <div class="log-entry__after">Bestand <?= (int) $b->bestand_nach ?></div>
                            </div>
                        <?= Html::endTag($istAdmin ? 'a' : 'div') ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <h2 class="section-title">Letzte 30 Tage</h2>
            <div class="stat-row stat-row--2">
                <div class="surface stat-chip" style="--chip-color: #9cc4ad;">
                    <div class="stat-chip__value">+<?= (int) $statistik['zugang'] ?></div>
                    <div class="stat-chip__label">Zugänge</div>
                </div>
                <div class="surface stat-chip" style="--chip-color: #e09a92;">
                    <div class="stat-chip__value">−<?= (int) $statistik['abgang'] ?></div>
                    <div class="stat-chip__label">Entnahmen</div>
                </div>
            </div>

            <h2 class="section-title">Details</h2>
            <dl class="surface info-list">
                <div class="info-list__row">
                    <dt class="info-list__label">Fachbereich</dt>
                    <dd class="info-list__value mb-0"><?= Html::encode($model->fachbereich->bezeichnung ?? '–') ?></dd>
                </div>
                <div class="info-list__row">
                    <dt class="info-list__label">Angelegt</dt>
                    <dd class="info-list__value mb-0"><?= date('d.m.Y', strtotime($model->erstellt_am)) ?></dd>
                </div>
                <div class="info-list__row">
                    <dt class="info-list__label">Zuletzt geändert</dt>
                    <dd class="info-list__value mb-0"><?= date('d.m.Y H:i', strtotime($model->aktualisiert_am)) ?></dd>
                </div>
            </dl>
        </div>
    </div>

    <?= $this->render('_buchungModal') ?>

</div>
