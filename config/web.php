<?php

$db = require __DIR__ . '/db.php';
$params = array_merge(
    require __DIR__ . '/params.php',
    file_exists(__DIR__ . '/params-local.php') ? require __DIR__ . '/params-local.php' : []
);

$config = [
    'id' => 'basic',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'container' => [
        'singletons' => [
            \yii\mail\MailerInterface::class => [
                'class' => \yii\symfonymailer\Mailer::class,
                // send all mails to a file by default.
                'useFileTransport' => true,
                'viewPath' => '@app/mail',
            ],
        ],
    ],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'session' => [
            'cookieParams' => ['httponly' => true, 'secure' => YII_ENV_PROD, 'sameSite' => 'Lax'],
        ],
        'request' => [
            // Set in config/params-local.php (not versioned). Yii refuses to start if empty.
            'cookieValidationKey' => $params['cookieValidationKey'] ?? '',
        ],
        'cache' => [
            'class' => \yii\caching\FileCache::class,
        ],
        'user' => [
            'identityClass' => \app\models\User::class,
            // SSO only: no remember-me cookie (identity lives in the server-side session)
            'enableAutoLogin' => false,
            'loginUrl' => ['site/login'],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => \yii\mail\MailerInterface::class,
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => \yii\log\FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
                // who exported what and when (CSV exports of the history)
                [
                    'class' => \yii\log\FileTarget::class,
                    'categories' => ['audit'],
                    'levels' => ['info'],
                    'logVars' => [],
                    'logFile' => '@runtime/logs/audit.log',
                    'maxFileSize' => 10240,
                    'maxLogFiles' => 20,
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
            ],
        ],
        'authClientCollection' => [
            'class' => 'yii\authclient\Collection',
            'clients' => [
                'azure' => [
                    'class' => 'chunlaw\authclient\Azure',
                    'authUrl' => 'https://login.microsoftonline.com/44f224f4-9869-4112-ac48-f18eb4419850/oauth2/v2.0/authorize?prompt=select_account',
                    'tokenUrl' => 'https://login.microsoftonline.com/44f224f4-9869-4112-ac48-f18eb4419850/oauth2/v2.0/token',
                    'returnUrl' => $params['azureReturnUrl'] ?? 'https://medino-inventur.ddev.site/site/auth?authclient=azure',
                    'clientId' => '54797bd5-6281-42f3-933f-115f5f047e75',
                    'clientSecret' => $params['azureClientSecret'] ?? '',
                ],
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => \yii\debug\Module::class,
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // localhost + Docker/DDEV networks only
        'allowedIPs' => ['127.0.0.1', '::1', '172.*'],
    ];
}

return $config;
