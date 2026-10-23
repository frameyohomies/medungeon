<?php

/**
 * Initials avatar. Colour follows the role (admin = accent, user = primary).
 *
 * @var yii\web\View $this
 * @var app\models\Benutzer $model
 * @var int|null $size
 */

use yii\helpers\Html;

$initialen = mb_strtoupper(
    mb_substr(trim((string) $model->firstname), 0, 1) . mb_substr(trim((string) $model->lastname), 0, 1)
) ?: '?';

echo Html::tag('span', Html::encode($initialen), [
    'class' => 'avatar' . ($model->aktiv ? '' : ' avatar--inactive'),
    'style' => isset($size) ? "--avatar-size: {$size}px;" : null,
    'aria-hidden' => 'true',
]);
