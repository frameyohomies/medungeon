<?php

namespace app\controllers;

use app\models\Produkt;
use app\models\ProduktSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

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
                        'deaktivieren' => ['POST'],
                        'aktivieren' => ['POST'],
                        'buchen' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        // product master data: admins only
                        [
                            'actions' => ['create', 'update', 'deaktivieren', 'aktivieren'],
                            'allow' => true,
                            'roles' => ['@'],
                            'matchCallback' => fn () => (bool) Yii::$app->user->identity?->isRolleAdmin(),
                        ],
                        // viewing, scanning and booking: any signed-in user
                        [
                            'actions' => ['index', 'view', 'lookup', 'buchen'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
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
        $model->loadDefaultValues();
        $anfangsbestand = 0;

        if ($this->request->isPost && $model->load($this->request->post())) {
            $anfangsbestand = max(0, (int) $this->request->post('anfangsbestand', 0));
            $model->quantitaet = 0;

            $transaction = Produkt::getDb()->beginTransaction();
            try {
                if ($model->save()) {
                    // opening stock is booked, so the history starts at the very first unit
                    if ($anfangsbestand === 0 || $model->buche($anfangsbestand, (int) Yii::$app->user->id)) {
                        $transaction->commit();
                        Yii::$app->session->setFlash('success', "„{$model->name}“ wurde angelegt.");
                        return $this->redirect(['view', 'id' => $model->id]);
                    }
                }
                $transaction->rollBack();
                $model->setIsNewRecord(true);
                $model->id = null;
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
        }

        return $this->render('create', [
            'model' => $model,
            'anfangsbestand' => $anfangsbestand,
        ]);
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Änderungen gespeichert.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Products are never deleted (their history must stay traceable) - deactivated instead.
     * Deactivated products are hidden from the list and cannot be scanned or booked.
     */
    public function actionDeaktivieren($id)
    {
        $model = $this->findModel($id);
        $model->updateAttributes(['aktiv' => 0]);
        Yii::$app->session->setFlash('success', "„{$model->name}“ wurde deaktiviert.");

        return $this->redirect(['index']);
    }

    public function actionAktivieren($id)
    {
        $model = $this->findModel($id);
        $model->updateAttributes(['aktiv' => 1]);
        Yii::$app->session->setFlash('success', "„{$model->name}“ wurde wieder aktiviert.");

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionBuchen($id)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $produkt = $this->findModel($id);
        $delta = (int) Yii::$app->request->post('delta');
        $benutzerId = (int) Yii::$app->user->id;

        $erfolg = $produkt->buche($delta, $benutzerId);

        return [
            'success' => $erfolg,
            'neuerBestand' => $produkt->quantitaet,
            'message' => $erfolg ? null : $produkt->getFirstError('quantitaet'),
        ];
    }

    /**
     * Finds an active product by barcode (scanner) or id ("Buchen" button on a card).
     */
    public function actionLookup($barcode = null, $id = null)
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $bedingung = $id !== null && $id !== ''
            ? ['id' => (int) $id]
            : ['barcode' => (string) $barcode];
        $produkt = ($barcode === null && ($id === null || $id === ''))
            ? null
            : Produkt::findOne($bedingung + ['aktiv' => 1]);

        if (!$produkt) {
            return ['success' => false];
        }

        return [
            'success' => true,
            'id' => $produkt->id,
            'name' => $produkt->name,
            'quantitaet' => $produkt->quantitaet,
            'mindestbestand' => $produkt->mindestbestand,
            'barcode' => $produkt->barcode,
            'standort' => $produkt->standort,
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
