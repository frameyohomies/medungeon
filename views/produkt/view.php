<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Produkt $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Produkts'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="produkt-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity?->isRolleAdmin()): ?>
    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php if ($model->aktiv): ?>
            <?= Html::a('Deaktivieren', ['deaktivieren', 'id' => $model->id], [
                'class' => 'btn btn-outline-danger',
                'data' => [
                    'confirm' => 'Produkt deaktivieren? Der Verlauf bleibt erhalten.',
                    'method' => 'post',
                ],
            ]) ?>
        <?php else: ?>
            <?= Html::a('Aktivieren', ['aktivieren', 'id' => $model->id], [
                'class' => 'btn btn-outline-success',
                'data' => ['method' => 'post'],
            ]) ?>
        <?php endif; ?>
    </p>
    <?php endif; ?>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'name',
            'barcode',
            'standort',
            'quantitaet',
            'mindestbestand',
            'aktiv:boolean',
            'erstellt_am',
            'aktualisiert_am',
        ],
    ]) ?>

</div>
