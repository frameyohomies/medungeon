<?php

namespace app\controllers;

use Yii;
use app\models\BestandBewegung;
use app\models\BestandBewegungSearch;
use yii\web\Controller;
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
     * Exports the (filtered) history as CSV for audits. Same filters as the index page.
     */
    public function actionExport(): \yii\web\Response
    {
        $searchModel = new BestandBewegungSearch();
        $dataProvider = $searchModel->search($this->request->queryParams, null, false);

        $fh = fopen('php://temp', 'r+');
        fwrite($fh, "\xEF\xBB\xBF"); // UTF-8 BOM so Excel shows umlauts correctly
        fputcsv($fh, ['ID', 'Zeitpunkt', 'Produkt', 'Barcode', 'Menge', 'Bestand danach', 'Benutzer', 'E-Mail'], ';');

        foreach ($dataProvider->query->batch(500) as $rows) {
            foreach ($rows as $m) {
                /** @var BestandBewegung $m */
                fputcsv($fh, [
                    $m->id,
                    $m->gebucht_am,
                    $m->produkt->name ?? '',
                    $m->produkt->barcode ?? '',
                    $m->delta,
                    $m->bestand_nach,
                    $m->benutzer ? trim($m->benutzer->firstname . ' ' . $m->benutzer->lastname) : '',
                    $m->benutzer->email ?? '',
                ], ';');
            }
        }

        rewind($fh);
        $csv = stream_get_contents($fh);
        fclose($fh);

        return Yii::$app->response->sendContentAsFile(
            $csv,
            'verlauf_' . date('Y-m-d_His') . '.csv',
            ['mimeType' => 'text/csv; charset=UTF-8']
        );
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
