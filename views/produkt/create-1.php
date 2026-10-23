<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Produkt $model */
/** @var int $anfangsbestand */

$this->title = 'Neues Produkt';
$this->params['breadcrumbs'][] = ['label' => 'Bestand', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="produkt-create">

    <div class="page-head">
        <div>
            <h1 class="page-head__title"><?= Html::encode($this->title) ?></h1>
            <p class="page-head__sub">Neues Produkt in den Bestand aufnehmen</p>
        </div>
    </div>

    <?= $this->render('_form', ['model' => $model, 'anfangsbestand' => $anfangsbestand]) ?>

</div>
