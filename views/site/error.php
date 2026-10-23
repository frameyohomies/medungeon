<?php

declare(strict_types=1);

/** @var yii\web\View $this */
/** @var string $name */
/** @var string $message */
/** @var Exception $exception */

use yii\helpers\Html;
use yii\web\HttpException;

$this->title = $name;
$statusCode = $exception instanceof HttpException ? $exception->statusCode : 500;
?>
<div class="site-error d-flex align-items-center justify-content-center text-center">
    <div class="site-error-content mx-auto">
        <h1 class="display-1 fw-bold text-body-secondary mb-0"><?= Html::encode($statusCode) ?></h1>

        <h2 class="display-6 fw-semibold mb-3"><?= Html::encode($message) ?></h2>

        <p class="text-body-secondary mb-4">
            <?php if ($statusCode === 403): ?>
                Für diese Seite fehlt dir die Berechtigung.
            <?php elseif ($statusCode === 404): ?>
                Diese Seite gibt es nicht (mehr).
            <?php else: ?>
                Da ist etwas schiefgelaufen. Bitte versuche es erneut oder melde dich bei der IT.
            <?php endif; ?>
        </p>

        <?= Html::a(
            'Zur Startseite',
            Yii::$app->homeUrl,
            ['class' => 'btn btn-outline-primary btn-lg'],
        ) ?>
    </div>
</div>
