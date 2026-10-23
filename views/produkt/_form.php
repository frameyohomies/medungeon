<?php

use app\models\Fachbereich;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Produkt $model */
/** @var int $anfangsbestand only used when creating */

$neu = $model->isNewRecord;
$fachbereiche = ArrayHelper::map(
    Fachbereich::find()->orderBy(['bezeichnung' => SORT_ASC])->all(),
    'id',
    'bezeichnung'
);
?>

<?php $form = ActiveForm::begin([
    'options' => ['class' => 'produkt-form'],
    'fieldConfig' => ['options' => ['class' => 'mb-3']],
]); ?>

<div class="row g-3">
    <div class="col-lg-7">
        <section class="surface form-section">
            <h2 class="form-section__title">Produkt</h2>

            <?= $form->field($model, 'name')->textInput([
                'maxlength' => true,
                'autofocus' => $neu,
                'placeholder' => 'z. B. Einmalhandschuhe Nitril M (Box)',
            ]) ?>

            <?= $form->field($model, 'barcode', [
                'inputTemplate' => '<div class="input-group">{input}<button type="button" class="btn btn-ghost-light" id="barcodeScanFeld" title="In das Feld klicken und mit dem Handscanner scannen">Scanner</button></div>',
            ])->textInput([
                'maxlength' => true,
                'autocomplete' => 'off',
                'placeholder' => 'Optional – mit Handscanner einscannen',
                // hand scanners send Enter: don't submit the form by accident
                'onkeydown' => "if (event.key === 'Enter') { event.preventDefault(); }",
            ])->hint('Ohne Barcode kann das Produkt nur über „Buchen“ in der Liste gebucht werden.') ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <?= $form->field($model, 'fachbereich_id')->dropDownList($fachbereiche, [
                        'prompt' => '– kein Fachbereich –',
                    ])->label('Fachbereich') ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'standort')->textInput([
                        'maxlength' => true,
                        'placeholder' => 'z. B. Lager Regal A, Fach 1',
                    ]) ?>
                </div>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="surface form-section">
            <h2 class="form-section__title">Bestand</h2>

            <?php if ($neu): ?>
                <div class="mb-3">
                    <?= Html::label('Anfangsbestand', 'anfangsbestand', ['class' => 'form-label']) ?>
                    <?= Html::input('number', 'anfangsbestand', $anfangsbestand ?? 0, [
                        'id' => 'anfangsbestand',
                        'class' => 'form-control',
                        'min' => 0,
                        'step' => 1,
                        'inputmode' => 'numeric',
                    ]) ?>
                    <div class="form-text">Wird als erste Buchung im Verlauf gespeichert.</div>
                </div>
            <?php else: ?>
                <div class="form-readonly mb-3">
                    <div class="form-label mb-1">Aktueller Bestand</div>
                    <div class="form-readonly__value"><?= (int) $model->quantitaet ?></div>
                    <div class="form-text">Ändert sich nur über „Buchen“ – so bleibt jede Änderung im Verlauf nachvollziehbar.</div>
                </div>
            <?php endif; ?>

            <?= $form->field($model, 'mindestbestand')->input('number', [
                'min' => 0,
                'step' => 1,
                'inputmode' => 'numeric',
            ])->hint('Fällt der Bestand auf oder unter diesen Wert, wird „Nachbestellen“ angezeigt.') ?>
        </section>
    </div>
</div>

<div class="form-actions">
    <?= Html::a('Abbrechen', $neu ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-link']) ?>
    <?= Html::submitButton($neu ? 'Produkt anlegen' : 'Speichern', ['class' => 'btn btn-primary btn-lg']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php $this->registerJs(<<<'JS'
document.getElementById('barcodeScanFeld')?.addEventListener('click', function () {
    const input = document.getElementById('produkt-barcode');
    input.focus();
    input.select();
});
JS); ?>
