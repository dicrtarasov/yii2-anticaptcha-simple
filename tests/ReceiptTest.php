<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 28.10.20 01:37:21
 */

namespace dicr\tests;

use dicr\anticaptcha\simple\AntiCaptchaSimpleModule;
use PHPUnit\Framework\TestCase;
use Yii;

/**
 * Class ReceiptTest
 */
class ReceiptTest extends TestCase
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
}
