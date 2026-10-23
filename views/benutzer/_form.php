<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Benutzer $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="benutzer-form">

    <?php $form = ActiveForm::begin(); ?>

    <p class="text-muted small">
        Name, E-Mail und Rolle werden bei jedem Login aus Microsoft Entra übernommen.
        Admin ist, wer im Tenant die Rolle <em>Global Administrator</em> hat.
    </p>

    <?= $form->field($model, 'aktiv')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
