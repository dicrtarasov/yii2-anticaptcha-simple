<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 28.10.20 14:47:04
 */

namespace dicr\tests;

use dicr\anticaptcha\simple\AntiCaptchaSimpleModule;
use dicr\anticaptcha\simple\ResultRequest;
use PHPUnit\Framework\TestCase;
use Yii;
use yii\base\Exception;

use function preg_match;

/**
 * Class ReceiptTest
 */
class CaptchaRequestTest extends TestCase
{
    /**
     * Модуль.
     *
     * @return AntiCaptchaSimpleModule
     */
    private static function module() : AntiCaptchaSimpleModule
    {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return Yii::$app->getModule('anticaptcha');
    }

    /**
     * @throws Exception
     */
    public function testCaptchaRequest() : void
    {
        // включаем режим json
        $module = self::module();
        $module->json = true;

        // создание капчи
        $req = $module->captchaRequest([
            'textCaptcha' => 'Привет'
        ]);

        $res = $req->send();
        echo 'Status: ' . $res->status . "\n";
        echo 'Request: ' . $res->request . "\n";
        self::assertTrue((bool)$res->status);
    }

    /**
     * @throws Exception
     */
    public function testResultRequest() : void
    {
        $module = self::module();

        $req = $module->resultRequest([
            'action' => ResultRequest::ACTION_GET_BALANCE
        ]);

        $res = $req->send();
        self::assertTrue((bool)$res->status);
        self::assertTrue((bool)preg_match('~^\d+~u', (string)$res->request));
        echo 'Баланс: ' . $res->request . "\n";
    }
}
