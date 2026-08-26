<?php

use app\models\Produkt;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
use app\assets\TablerIconsAsset;
TablerIconsAsset::register($this);

/** @var yii\web\View $this */
/** @var app\models\ProduktSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = Yii::t('app', 'Produkts');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="produkt-index">
    <h1><?= Html::encode($this->title) ?></h1>

    <?php if (Yii::$app->user->identity->rolle === 'admin'): ?>
        <p><?= Html::a('Create Produkt', ['create'], ['class' => 'btn btn-success']) ?></p>
    <?php endif; ?>

    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#scanModal">
        Scannen
    </button>

    <div class="row g-3">
        <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 produkt-card <?= $model->quantitaet <= $model->mindestbestand ? 'produkt-card--warn' : '' ?>">
                    <div class="produkt-card__accent"></div>
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="produkt-card__title mb-0"><?= Html::encode($model->name) ?></h5>
                            <?php if ($model->quantitaet <= $model->mindestbestand): ?>
                                <span class="produkt-card__badge">Nachbestellen</span>
                            <?php endif; ?>
                        </div>

                        <div class="produkt-card__row">
                            <i class="bi bi-geo-alt-fill produkt-card__icon"></i>
                            <span><?= Html::encode($model->standort) ?></span>
                        </div>

                        <div class="produkt-card__row produkt-card__row--quantity">
                            <i class="bi bi-box-seam-fill produkt-card__icon"></i>
                            <span class="produkt-card__number <?= $model->quantitaet <= $model->mindestbestand ? 'text-danger' : '' ?>">
                <?= $model->quantitaet ?>
            </span>
                            <span class="text-muted">/ Mindest: <?= $model->mindestbestand ?></span>
                        </div>

                    </div>
                    <div class="card-footer produkt-card__footer">
                        <?= Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-eye" viewBox="0 0 16 16">
  <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
  <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
</svg>', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                        <?php if (Yii::$app->user->identity->rolle === 'admin'): ?>
                            <?= Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil" viewBox="0 0 16 16">
  <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325"/>
</svg>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-warning']) ?>
                            <?= Html::a('<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">
  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5m3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0z"/>
  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4zM2.5 3h11V2h-11z"/>
</svg>', ['delete', 'id' => $model->id], [
                                'class' => 'btn btn-sm btn-outline-danger',
                                'data' => ['confirm' => 'Wirklich löschen?', 'method' => 'post'],
                            ]) ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?= yii\widgets\LinkPager::widget([
        'pagination' => $dataProvider->getPagination(),
    ]) ?>

</div>
