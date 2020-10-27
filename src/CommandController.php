<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 28.10.20 01:38:27
 */

declare(strict_types = 1);
namespace dicr\anticaptcha\simple;

use yii\base\Exception;
use yii\console\Controller;

use function var_dump;

/**
 * Контроллер консольных команд.
 *
 * @property-read AntiCaptchaSimpleModule $module
 * @link https://rucaptcha.com/api-rucaptcha#manage_pingback
 */
class CommandController extends Controller
{
    /**
     * Зарегистрировать callback-адрес.
     *
     * @throws Exception
     */
    public function actionRegisterCallback() : void
    {
        $res = $this->module->resultRequest([
            'action' => 'add_pingback',
            'addr' => $this->module->callbackUrl()
        ]);

        $response = $res->send();

        /** @noinspection ForgottenDebugOutputInspection */
        var_dump($response);
    }

    /**
     * Получить список зарегистрированных callback-адресов.
     *
     * @throws Exception
     */
    public function actionGetCallback() : void
    {
        $res = $this->module->resultRequest([
            'action' => 'get_pingback',
        ]);

        $response = $res->send();

        /** @noinspection ForgottenDebugOutputInspection */
        var_dump($response);
    }

    /**
     * Удалить адрес callback.
     *
     * @throws Exception
     */
    public function actionUnregisterCallback() : void
    {
        $res = $this->module->resultRequest([
            'action' => 'del_pingback',
            'addr' => $this->module->callbackUrl()
        ]);

        $response = $res->send();

        /** @noinspection ForgottenDebugOutputInspection */
        var_dump($response);
    }
}
