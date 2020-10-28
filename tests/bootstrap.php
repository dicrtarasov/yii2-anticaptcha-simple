<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 28.10.20 14:38:46
 */

declare(strict_types = 1);

/** среда разработки */
defined('YII_ENV') || define('YII_ENV', 'dev');

/** режим отладки */
defined('YII_DEBUG') || define('YII_DEBUG', true);

require_once(dirname(__DIR__) . '/vendor/autoload.php');
require_once(dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php');

/** @noinspection PhpUnhandledExceptionInspection */
new yii\console\Application([
    'id' => 'test',
    'basePath' => dirname(__DIR__),
    'components' => [
        'urlManager' => [
            'hostInfo' => 'https://dicr.org',
            'baseUrl' => '',
            'scriptUrl' => '/index.php'
        ],
        'cache' => [
            'class' => yii\caching\FileCache::class
        ],
        'log' => [
            'flushInterval' => 1,
            'targets' => [
                [
                    'class' => yii\log\FileTarget::class,
                    'levels' => ['error', 'warning', 'info', 'trace']
                ]
            ]
        ]
    ],
    'modules' => [
        'anticaptcha' => [
            'class' => dicr\anticaptcha\simple\AntiCaptchaSimpleModule::class,
            'key' => '96edc988b29b84fdce67ae9433febf05'
        ]
    ],
    'bootstrap' => ['log']
]);
