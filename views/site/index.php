<?php
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Login';
?>
<div class="site-index">

    <!-- Hero banner mit eurer Farbpalette -->
    <div class="hero-banner text-white rounded-4 p-5 mb-4 position-relative overflow-hidden d-flex flex-column align-items-center text-center">
        <div class="position-relative py-4">
            <h1 class="display-5 fw-bold mb-3">medungeon</h1>
            <p class="lead opacity-75 mb-4">
                Inventurverwaltung fürs Lager – schnell, nachvollziehbar, überall am Handy.
            </p>

            <?php if (Yii::$app->user->isGuest): ?>
                <p class="opacity-75 mb-3 small">Melde dich mit deinem Firmenkonto an</p>
                <a href="<?= Url::to(['site/auth', 'authclient' => 'azure']) ?>">
                    <img src="<?= Yii::getAlias('@web/images/ms-symbollockup_signin_light.svg') ?>" class="hero-banner-img" alt="Sign in with Microsoft" height="41">
                </a>
            <?php else: ?>
                <p class="mb-3">Eingeloggt als <?= Html::encode(Yii::$app->user->identity->firstname) ?></p>
                <?= Html::a('Zum Bestand', ['produkt/index'], ['class' => 'btn btn-light btn-lg fw-semibold px-4']) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Feature-Übersicht -->
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-3 extension-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="extension-icon" aria-hidden="true">&#128241;</span>
                        <h3 class="h6 fw-bold mb-0 ms-2">Barcode-Scan</h3>
                    </div>
                    <p class="text-body-secondary small mb-0">
                        Produkte per Handy-Kamera scannen und Bestand direkt vor Ort anpassen.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-3 extension-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="extension-icon" aria-hidden="true">&#128230;</span>
                        <h3 class="h6 fw-bold mb-0 ms-2">Bestandsübersicht</h3>
                    </div>
                    <p class="text-body-secondary small mb-0">
                        Aktuelle Mengen aller Produkte auf einen Blick, inklusive Mindestbestand-Warnung.
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm rounded-3 extension-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <span class="extension-icon" aria-hidden="true">&#128340;</span>
                        <h3 class="h6 fw-bold mb-0 ms-2">Verlauf</h3>
                    </div>
                    <p class="text-body-secondary small mb-0">
                        Jede Buchung nachvollziehbar: wer, wann, wie viel.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
