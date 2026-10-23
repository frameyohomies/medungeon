<?php

declare(strict_types=1);

namespace app\controllers;

use Yii;
use app\models\ContactForm;
use yii\captcha\CaptchaAction;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\mail\MailerInterface;
use yii\web\Controller;
use yii\web\ErrorAction;
use yii\web\Response;
use yii\authclient\ClientInterface;
use yii\web\ForbiddenHttpException;
use app\models\Benutzer;
use app\models\User;

class SiteController extends Controller
{
    public function __construct(
        $id,
        $module,
        private readonly MailerInterface $mailer,
        $config = [],
    ) {
        parent::__construct($id, $module, $config);
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
            'captcha' => [
                'class' => CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
                'transparent' => true,
            ],
            'auth' => [
                'class' => \yii\authclient\AuthAction::class,
                'successCallback' => [$this, 'onAuthSuccess'],
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        return $this->render('index');
    }

    /**
     * Login page: only offers "Sign in with Microsoft" (SSO via Entra ID).
     */
    public function actionLogin(): Response|string
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        return $this->render('login');
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout(): Response
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact(): Response|string
    {
        $model = new ContactForm();

        $contact = $model->load($this->request->post()) && $model->contact(
            $this->mailer,
            Yii::$app->params['adminEmail'],
            Yii::$app->params['senderEmail'],
            Yii::$app->params['senderName'],
        );

        if ($contact) {
            Yii::$app->session->setFlash(
                'success',
                'Thank you for contacting us. We will respond to you as soon as possible.',
            );

            return $this->refresh();
        }

        return $this->render('contact', ['model' => $model]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout(): string
    {
        return $this->render('about');
    }

    public function onAuthSuccess(ClientInterface $client): Response
    {
        $attributes = $client->getUserAttributes();
        $oid = $attributes['id'] ?? null;

        if (empty($oid)) {
            throw new ForbiddenHttpException('Microsoft-Anmeldung lieferte keine Benutzer-ID.');
        }

        $benutzer = User::findOne(['entra_oid' => $oid]);
        if (!$benutzer) {
            $benutzer = new User();
            $benutzer->entra_oid = $oid;
            $benutzer->aktiv = 1;
        }

        if ((int) $benutzer->aktiv !== 1) {
            Yii::warning("Login abgelehnt: Benutzer {$oid} ist deaktiviert.", __METHOD__);
            throw new ForbiddenHttpException('Ihr Zugang ist deaktiviert.');
        }

        // keep profile data in sync with the tenant on every login
        $benutzer->firstname = $attributes['givenName'] ?? $benutzer->firstname ?? '';
        $benutzer->lastname = $attributes['surname'] ?? $benutzer->lastname ?? '';
        $benutzer->email = $attributes['mail'] ?? $attributes['userPrincipalName'] ?? $benutzer->email ?? '';
        $benutzer->rolle = $this->pruefeGlobalAdmin($client, $oid) ? Benutzer::ROLLE_ADMIN : Benutzer::ROLLE_USER;

        if (!$benutzer->save()) {
            Yii::error(['Benutzer konnte nicht gespeichert werden', $benutzer->errors], __METHOD__);
            throw new ForbiddenHttpException('Anmeldung fehlgeschlagen.');
        }

        // session only (no remember-me cookie); login() also regenerates the session id
        Yii::$app->user->login($benutzer);

        return $this->goHome();
    }

    /**
     * True if the user holds the Entra "Global Administrator" directory role.
     * Fails closed (non-admin) if Graph cannot be queried.
     */
    private function pruefeGlobalAdmin(ClientInterface $client, string $userId): bool
    {
        $globalAdminTemplateId = '62e90394-69f5-4237-9190-012177145e10';
        $url = 'users/' . rawurlencode($userId) . '/memberOf/microsoft.graph.directoryRole';

        try {
            while ($url) {
                $response = $client->api($url, 'GET');
                foreach ($response['value'] ?? [] as $eintrag) {
                    if (($eintrag['roleTemplateId'] ?? '') === $globalAdminTemplateId) {
                        return true;
                    }
                }
                $url = $response['@odata.nextLink'] ?? null;
            }
        } catch (\Throwable $e) {
            Yii::error($e, __METHOD__);
        }

        return false;
    }

}
