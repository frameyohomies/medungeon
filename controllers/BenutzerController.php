<?php

namespace app\controllers;

use Yii;
use app\models\Benutzer;
use app\models\BenutzerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * BenutzerController: admin view of users.
 *
 * Users are provisioned from the Entra tenant on first login (SiteController::onAuthSuccess),
 * name/email/role are synced on every login. Users are never deleted (their bookings must
 * stay traceable) - they are deactivated instead.
 */
class BenutzerController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::class,
                    'actions' => [
                        'deaktivieren' => ['POST'],
                        'aktivieren' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'rules' => [
                        [
                            'allow' => true,
                            'roles' => ['@'],
                            'matchCallback' => fn () => (bool) Yii::$app->user->identity?->isRolleAdmin(),
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * Lists all Benutzer models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new BenutzerSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Benutzer model.
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
     * Updates an existing Benutzer model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $aktiv = (int) (bool) ($this->request->post($model->formName())['aktiv'] ?? $model->aktiv);
            if ($aktiv === 0 && (int) $model->id === (int) Yii::$app->user->id) {
                Yii::$app->session->setFlash('error', 'Du kannst dich nicht selbst deaktivieren.');
            } else {
                $model->updateAttributes(['aktiv' => $aktiv]);
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deactivates a user: blocks login and ends active sessions on the next request.
     */
    public function actionDeaktivieren($id)
    {
        $model = $this->findModel($id);

        if ((int) $model->id === (int) Yii::$app->user->id) {
            Yii::$app->session->setFlash('error', 'Du kannst dich nicht selbst deaktivieren.');
        } else {
            $model->updateAttributes(['aktiv' => 0]);
            Yii::$app->session->setFlash('success', 'Benutzer deaktiviert.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    public function actionAktivieren($id)
    {
        $model = $this->findModel($id);
        $model->updateAttributes(['aktiv' => 1]);
        Yii::$app->session->setFlash('success', 'Benutzer aktiviert.');

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Finds the Benutzer model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Benutzer the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Benutzer::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
