<?php

namespace app\controllers;

use app\models\BestandBewegung;
use app\models\BestandBewegungSearch;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * BestandBewegungController: read-only view of the stock history.
 *
 * Entries are written exclusively via Produkt::buche() to keep traceability;
 * there are intentionally no create/update/delete actions.
 */
class BestandBewegungController extends Controller
{
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
