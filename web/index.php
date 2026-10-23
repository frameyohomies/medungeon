<?php

declare(strict_types=1);

// Environment comes from the server (DDEV/docker set YII_ENV=dev); defaults to production.
$yiiEnv = getenv('YII_ENV') ?: 'prod';
defined('YII_ENV') or define('YII_ENV', $yiiEnv);
defined('YII_DEBUG') or define('YII_DEBUG', $yiiEnv === 'dev');

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../vendor/yiisoft/yii2/Yii.php';

$config = require __DIR__ . '/../config/web.php';

(new yii\web\Application($config))->run();
