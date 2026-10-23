<?php

namespace app\controllers;

use Yii;
use app\models\BestandBewegung;
use app\models\BestandBewegungSearch;
use app\components\VerlaufCsvExport;
use yii\web\Controller;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

/**
 * BestandBewegungController: read-only stock history.
 *
 * Entries are written exclusively via Produkt::buche() for traceability;
 * there are intentionally no create/update/delete actions.
 */
class BestandBewegungController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors(): array
    {
        return array_merge(
            parent::behaviors(),
            [
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
     * Lists all BestandBewegung models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new BestandBewegungSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single BestandBewegung model.
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
     * Exports the filtered history as CSV (same filters as the list, all pages).
     * Every export is written to the audit log (runtime/logs/audit.log).
     */
    public function actionExport(): Response
    {
        $searchModel = new BestandBewegungSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, null, false);

        if ($searchModel->hasErrors()) {
            Yii::$app->session->setFlash('error', 'Export nicht möglich: ' . implode(' ', $searchModel->getErrorSummary(true)));
            return $this->redirect(array_merge(['index'], $this->request->queryParams));
        }

        $export = VerlaufCsvExport::write($dataProvider->query);

        $user = Yii::$app->user->identity;
        Yii::info([
            'event' => 'verlauf_export',
            'benutzer_id' => $user?->id,
            'email' => $user?->email,
            'rows' => $export['rows'],
            'filter' => array_filter([
                'produkt_id' => $searchModel->produkt_id,
                'benutzer_id' => $searchModel->benutzer_id,
                'produktName' => $searchModel->produktName,
                'benutzerName' => $searchModel->benutzerName,
                'von' => $searchModel->von,
                'bis' => $searchModel->bis,
            ], static fn ($v) => $v !== null && $v !== ''),
            'ip' => $this->request->userIP,
        ], 'audit');

        return Yii::$app->response->sendStreamAsFile(
            $export['stream'],
            $this->exportDateiname($searchModel),
            ['mimeType' => 'text/csv; charset=UTF-8']
        );
    }

    private function exportDateiname(BestandBewegungSearch $s): string
    {
        $teile = ['verlauf'];
        if ($s->von || $s->bis) {
            $teile[] = ($s->von ?: 'anfang') . '_bis_' . ($s->bis ?: date('Y-m-d'));
        } else {
            $teile[] = 'stand_' . date('Y-m-d');
        }
        if ($s->produkt_id || $s->benutzer_id || $s->produktName || $s->benutzerName) {
            $teile[] = 'gefiltert';
        }

        return implode('_', $teile) . '.csv';
    }

    /**
     * Finds the BestandBewegung model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return BestandBewegung the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = BestandBewegung::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
    }
}
