<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\models\Benutzer $model */

$this->title = $model->firstname . ' ' . $model->lastname;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Benutzer'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="benutzer-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?php if ($model->aktiv): ?>
            <?= Html::a('Deaktivieren', ['deaktivieren', 'id' => $model->id], [
                'class' => 'btn btn-outline-danger',
                'data' => [
                    'confirm' => 'Benutzer deaktivieren? Die Person kann sich danach nicht mehr anmelden. Ihre Buchungen bleiben erhalten.',
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

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'entra_oid',
            'firstname',
            'lastname',
            'email:email',
            'rolle',
            'aktiv:boolean',
            'erstellt_am',
        ],
    ]) ?>

</div>
