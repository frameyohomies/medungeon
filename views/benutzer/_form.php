<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\Benutzer $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="benutzer-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'firstname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'lastname')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'rolle')->dropDownList([ 'admin' => 'Admin', 'user' => 'Mitarbeiter', ], ['prompt' => '']) ?>

    <?= $form->field($model, 'aktiv')->checkbox() ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
