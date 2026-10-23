<?php

use app\models\BenutzerSearch;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var app\models\BenutzerSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Benutzer';
$this->params['breadcrumbs'][] = $this->title;

$z = BenutzerSearch::zaehler();
$aktuellerFilter = $searchModel->filter ?: BenutzerSearch::FILTER_ALLE;
$segmente = [
    BenutzerSearch::FILTER_ALLE => 'Alle',
    BenutzerSearch::FILTER_ADMINS => 'Admins',
    BenutzerSearch::FILTER_DEAKTIVIERT => 'Deaktiviert',
];
?>
<div class="benutzer-index">

    <div class="page-head">
        <div>
            <h1 class="page-head__title"><?= Html::encode($this->title) ?></h1>
            <p class="page-head__sub">Konten aus Microsoft Entra – Name, E-Mail und Rolle werden bei jedem Login synchronisiert.</p>
        </div>
    </div>

    <div class="stat-row">
        <div class="surface stat-chip">
            <div class="stat-chip__value"><?= $z['aktiv'] ?></div>
            <div class="stat-chip__label">Aktiv</div>
        </div>
        <div class="surface stat-chip" style="--chip-color: var(--color-accent);">
            <div class="stat-chip__value"><?= $z['admins'] ?></div>
            <div class="stat-chip__label">Admins</div>
        </div>
        <div class="surface stat-chip" style="--chip-color: #8a958f;">
            <div class="stat-chip__value"><?= $z['deaktiviert'] ?></div>
            <div class="stat-chip__label">Deaktiviert</div>
        </div>
    </div>

    <div class="surface filter-bar">
        <?php $form = ActiveForm::begin(['method' => 'get', 'action' => ['index']]); ?>
        <?= Html::activeHiddenInput($searchModel, 'filter') ?>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="flex-grow-1" style="min-width: 220px;">
                <?= Html::activeTextInput($searchModel, 'q', [
                    'class' => 'form-control',
                    'placeholder' => 'Name oder E-Mail suchen…',
                    'aria-label' => 'Benutzer suchen',
                ]) ?>
            </div>
            <?= Html::submitButton('Suchen', ['class' => 'btn btn-primary']) ?>
            <div class="seg ms-md-2">
                <?php foreach ($segmente as $key => $label): ?>
                    <?= Html::a(
                        Html::encode($label),
                        ['index', 'BenutzerSearch' => ['filter' => $key, 'q' => $searchModel->q]],
                        ['class' => 'seg__item' . ($aktuellerFilter === $key ? ' seg__item--active' : '')]
                    ) ?>
                <?php endforeach; ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>
    </div>

    <?php $models = $dataProvider->getModels(); ?>

    <?php if (!$models): ?>
        <div class="surface empty-state">
            <span class="empty-state__icon" aria-hidden="true">&#128100;</span>
            Keine Benutzer gefunden.
        </div>
    <?php else: ?>
        <div class="person-list">
            <?php foreach ($models as $m): ?>
                <?php $istAdmin = $m->isRolleAdmin(); ?>
                <a href="<?= Html::encode(\yii\helpers\Url::to(['view', 'id' => $m->id])) ?>"
                   class="surface person-row<?= $m->aktiv ? '' : ' person-row--inactive' ?>"
                   style="--avatar-color: <?= $istAdmin ? 'var(--color-accent)' : 'var(--color-primary)' ?>;">

                    <?= $this->render('_avatar', ['model' => $m]) ?>

                    <div class="person-row__main">
                        <div class="person-row__name"><?= Html::encode(trim($m->firstname . ' ' . $m->lastname)) ?></div>
                        <div class="person-row__mail"><?= Html::encode($m->email) ?></div>
                    </div>

                    <div class="person-row__meta">
                        <?php if (!$m->aktiv): ?>
                            <span class="pill pill--inactive">Deaktiviert</span>
                        <?php elseif ($istAdmin): ?>
                            <span class="pill pill--admin">Admin</span>
                        <?php else: ?>
                            <span class="pill pill--user">Mitarbeiter</span>
                        <?php endif; ?>
                        <span class="status-dot<?= $m->aktiv ? '' : ' status-dot--off' ?>"
                              title="<?= $m->aktiv ? 'Aktiv' : 'Deaktiviert' ?>"></span>
                        <span class="person-row__chev" aria-hidden="true">&rsaquo;</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="pager-wrap">
            <?= LinkPager::widget(['pagination' => $dataProvider->getPagination()]) ?>
        </div>
    <?php endif; ?>

</div>
