<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\BestandBewegung $model */

$this->title = 'Buchung #' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Verlauf', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="bestand-bewegung-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            [
                'label' => 'Zeitpunkt',
                'value' => $model->gebucht_am,
                'format' => ['datetime', 'php:d.m.Y H:i:s'],
            ],
            [
                'label' => 'Produkt',
                'format' => 'raw',
                'value' => $model->produkt
                    ? Html::a(Html::encode($model->produkt->name), ['/produkt/view', 'id' => $model->produkt_id])
                        . ($model->produkt->barcode ? ' <span class="text-muted">(' . Html::encode($model->produkt->barcode) . ')</span>' : '')
                    : '#' . $model->produkt_id,
            ],
            [
                'label' => 'Menge',
                'value' => ($model->delta > 0 ? '+' : '') . $model->delta,
            ],
            [
                'label' => 'Bestand danach',
                'value' => $model->bestand_nach,
            ],
            [
                'label' => 'Benutzer',
                'value' => $model->benutzer
                    ? trim($model->benutzer->firstname . ' ' . $model->benutzer->lastname) . ' <' . $model->benutzer->email . '>'
                    : '#' . $model->benutzer_id,
            ],
        ],
    ]) ?>

</div>
