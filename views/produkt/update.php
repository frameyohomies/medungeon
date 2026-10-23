<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Produkt $model */

$this->title = 'Bearbeiten: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Bestand', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Bearbeiten';
?>
<div class="produkt-update">

    <div class="page-head">
        <div>
            <h1 class="page-head__title"><?= Html::encode($model->name) ?></h1>
            <p class="page-head__sub">Produktdaten bearbeiten</p>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model]) ?>

</div>
