<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 27.10.20 22:36:03
 */

declare(strict_types = 1);
namespace dicr\anticaptcha\simple;

use Yii;
use yii\base\Exception;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Request;

use function call_user_func;

/**
 * Контроллер callback=запросов с сервиса капчи.
 *
 * @property-read Request $request
 * @property-read AntiCaptchaSimpleModule $module
 */
class CallbackController extends Controller
{
    /**
     * Индекс.
     *
     * @throws Exception
     */
    public function actionIndex() : void
    {
        if (! $this->request->isPost) {
            throw new BadRequestHttpException($this->request->method);
        }

        $id = (int)$this->request->post('id', 0);
        if (empty($id)) {
            throw new BadRequestHttpException('id');
        }

        $code = (string)$this->request->post('code', '');
        if ($code === '') {
            throw new BadRequestHttpException('code');
        }

        Yii::debug('Callback: id=' . $id . ', code=' . $code, __NAMESPACE__);

        if ($this->module->handler !== null) {
            call_user_func($this->module->handler, $id, $code);
        }
    }
}
