<?php

use app\models\BestandBewegung;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Benutzer $model */

$name = trim($model->firstname . ' ' . $model->lastname);
$this->title = $name ?: $model->email;
$this->params['breadcrumbs'][] = ['label' => 'Benutzer', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$istAdmin = $model->isRolleAdmin();
$istIch = (int) $model->id === (int) Yii::$app->user->id;

$statistik = BestandBewegung::find()
    ->where(['benutzer_id' => $model->id])
    ->select([
        'anzahl' => 'COUNT(*)',
        'zugang' => 'COALESCE(SUM(CASE WHEN delta > 0 THEN delta ELSE 0 END), 0)',
        'abgang' => 'COALESCE(SUM(CASE WHEN delta < 0 THEN -delta ELSE 0 END), 0)',
        'zuletzt' => 'MAX(gebucht_am)',
    ])
    ->asArray()
    ->one();

$letzte = BestandBewegung::find()
    ->where(['benutzer_id' => $model->id])
    ->with('produkt')
    ->orderBy(['gebucht_am' => SORT_DESC, 'id' => SORT_DESC])
    ->limit(8)
    ->all();
?>
<div class="benutzer-view">

    <div class="surface person-hero" style="--avatar-color: <?= $istAdmin ? 'var(--color-accent)' : 'var(--color-primary)' ?>;">
        <?= $this->render('_avatar', ['model' => $model, 'size' => 72]) ?>

        <div>
            <h1 class="person-hero__name"><?= Html::encode($this->title) ?></h1>
            <div class="person-hero__mail"><?= Html::encode($model->email) ?></div>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <span class="pill <?= $istAdmin ? 'pill--admin' : 'pill--user' ?>"><?= $istAdmin ? 'Admin' : 'Mitarbeiter' ?></span>
                <?php if ($model->aktiv): ?>
                    <span class="pill pill--inactive"><span class="status-dot"></span> Aktiv</span>
                <?php else: ?>
                    <span class="pill pill--inactive"><span class="status-dot status-dot--off"></span> Deaktiviert</span>
                <?php endif; ?>
                <?php if ($istIch): ?>
                    <span class="pill pill--inactive">Du</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="person-hero__actions">
            <?php if ($model->aktiv && !$istIch): ?>
                <?= Html::a('Deaktivieren', ['deaktivieren', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger',
                    'data' => [
                        'confirm' => 'Benutzer deaktivieren? Die Person kann sich danach nicht mehr anmelden. Ihre Buchungen bleiben erhalten.',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php elseif (!$model->aktiv): ?>
                <?= Html::a('Aktivieren', ['aktivieren', 'id' => $model->id], [
                    'class' => 'btn btn-outline-success',
                    'data' => ['method' => 'post'],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-row">
        <div class="surface stat-chip">
            <div class="stat-chip__value"><?= (int) $statistik['anzahl'] ?></div>
            <div class="stat-chip__label">Buchungen</div>
        </div>
        <div class="surface stat-chip" style="--chip-color: #9cc4ad;">
            <div class="stat-chip__value">+<?= (int) $statistik['zugang'] ?></div>
            <div class="stat-chip__label">Zugänge</div>
        </div>
        <div class="surface stat-chip" style="--chip-color: #e09a92;">
            <div class="stat-chip__value">−<?= (int) $statistik['abgang'] ?></div>
            <div class="stat-chip__label">Entnahmen</div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-7">
            <div class="d-flex justify-content-between align-items-baseline">
                <h2 class="section-title">Letzte Buchungen</h2>
                <?php if ($letzte): ?>
                    <?= Html::a(
                        'Alle im Verlauf &rsaquo;',
                        ['/bestand-bewegung/index', 'BestandBewegungSearch' => ['benutzer_id' => $model->id]],
                        ['class' => 'small']
                    ) ?>
                <?php endif; ?>
            </div>

            <?php if (!$letzte): ?>
                <div class="surface empty-state">Noch keine Buchungen.</div>
            <?php else: ?>
                <div class="surface log-list">
                    <?php foreach ($letzte as $b): ?>
                        <a class="log-entry" href="<?= Html::encode(Url::to(['/bestand-bewegung/view', 'id' => $b->id])) ?>">
                            <span class="delta <?= $b->delta > 0 ? 'delta--in' : 'delta--out' ?>">
                                <?= $b->delta > 0 ? '+' . $b->delta : '−' . abs($b->delta) ?>
                            </span>
                            <div style="min-width: 0;">
                                <div class="log-entry__product"><?= Html::encode($b->produkt->name ?? ('#' . $b->produkt_id)) ?></div>
                                <div class="log-entry__sub">Bestand danach: <?= (int) $b->bestand_nach ?></div>
                            </div>
                            <div class="log-entry__right">
                                <div class="log-entry__time"><?= date('H:i', strtotime($b->gebucht_am)) ?></div>
                                <div class="log-entry__after"><?= date('d.m.Y', strtotime($b->gebucht_am)) ?></div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-lg-5">
            <h2 class="section-title">Konto</h2>
            <dl class="surface info-list">
                <div class="info-list__row">
                    <dt class="info-list__label">Angelegt</dt>
                    <dd class="info-list__value mb-0"><?= date('d.m.Y', strtotime($model->erstellt_am)) ?></dd>
                </div>
                <div class="info-list__row">
                    <dt class="info-list__label">Letzte Buchung</dt>
                    <dd class="info-list__value mb-0">
                        <?= $statistik['zuletzt'] ? date('d.m.Y H:i', strtotime($statistik['zuletzt'])) : '–' ?>
                    </dd>
                </div>
                <div class="info-list__row">
                    <dt class="info-list__label">Entra Object-ID</dt>
                    <dd class="info-list__value mb-0"><code class="small"><?= Html::encode($model->entra_oid) ?></code></dd>
                </div>
            </dl>
            <p class="small text-body-secondary mt-2 mb-0">
                Admin ist, wer im Tenant die Rolle <em>Global Administrator</em> hat. Die Rolle wird beim nächsten Login aktualisiert.
            </p>
        </div>
    </div>

</div>
