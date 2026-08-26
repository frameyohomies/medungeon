<?php

namespace app\controllers;

use app\models\Produkt;
use app\models\ProduktSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProduktController implements the CRUD actions for Produkt model.
 */
class ProduktController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Produkt models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new ProduktSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Produkt model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Produkt model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Produkt();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Produkt model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Produkt model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionBuchen($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $produkt = $this->findModel($id);
        $delta = (int) Yii::$app->request->post('delta');
        $benutzerId = 2; // Platzhalter bis Login steht

        $erfolg = $produkt->buche($delta, $benutzerId);

        return [
            'success' => $erfolg,
            'neuerBestand' => $produkt->quantitaet,
        ];
    }

    public function actionLookup($barcode)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $produkt = Produkt::findOne(['barcode' => $barcode]);

        if (!$produkt) {
            return ['success' => false];
        }

        return [
            'success' => true,
            'id' => $produkt->id,
            'name' => $produkt->name,
            'quantitaet' => $produkt->quantitaet,
            'mindestbestand' => $produkt->mindestbestand,
        ];
    }

    /**
     * Finds the Produkt model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Produkt the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Produkt::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
