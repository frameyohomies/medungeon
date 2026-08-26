<?php

declare(strict_types=1);

/** @var yii\web\View $this */

use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Html;

$items = [
    [
        'label' => 'User',
        'url' => ['/benutzer/index'],
        'visible' => !Yii::$app->user->isGuest,
    ],
    [
        'label' => 'Bestand',
        'url' => ['/produkt/index'],
        'visible' => !Yii::$app->user->isGuest,
    ],
    [
        'label' => 'Verlauf',
        'url' => ['/bestand-bewegung/index'],
        'visible' => !Yii::$app->user->isGuest,
    ],
    [
        'label' => 'Logout (' . Html::encode(trim((Yii::$app->user->identity?->firstname ?? '') . ' ' . (Yii::$app->user->identity?->lastname ?? ''))) . ')',
        'url' => ['/site/logout'],
        'linkOptions' => [
            'data-method' => 'post',
            'class' => 'nav-link logout',
        ],
        'visible' => !Yii::$app->user->isGuest,
    ],
];

?>
<header id="header">
    <?php NavBar::begin(
        [
            'brandLabel' => Yii::$app->name,
            'brandUrl' => Yii::$app->homeUrl,
            'options' => ['class' => 'navbar-expand-md navbar-dark bg-dark fixed-top']
        ],
    ) ?>
    <?= Nav::widget(
        [
            'options' => ['class' => 'navbar-nav me-auto'],
            'encodeLabels' => false,
            'items' => $items,
        ],
    ) ?>

    <?php if (Yii::$app->user->isGuest): ?>
        <a href="<?= yii\helpers\Url::to(['site/auth', 'authclient' => 'azure']) ?>">
            <img src="<?= Yii::getAlias('@web/images/ms-symbollockup_signin_light.svg') ?>" alt="Sign in with Microsoft" height="35">
        </a>
    <?php endif; ?>

    <?= Html::button(
        '&#127769;',
        [
            'id' => 'theme-toggle',
            'class' => 'btn btn-link nav-link fs-5',
            'aria-label' => 'Switch to dark mode',
        ],
    ) ?>
    <?php NavBar::end() ?>
</header>
