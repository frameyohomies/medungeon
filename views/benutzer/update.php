<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Benutzer $model */

$name = trim($model->firstname . ' ' . $model->lastname);
$this->title = 'Bearbeiten: ' . $name;
$this->params['breadcrumbs'][] = ['label' => 'Benutzer', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Bearbeiten';
?>
<div class="benutzer-update" style="max-width: 560px;">

    <h1 class="page-head__title mb-3"><?= Html::encode($this->title) ?></h1>

    <div class="surface p-3">
        <?= $this->render('_form', ['model' => $model]) ?>
    </div>

</div>
