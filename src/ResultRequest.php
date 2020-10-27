<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 28.10.20 01:33:21
 */

declare(strict_types = 1);
namespace dicr\anticaptcha\simple;

use dicr\helper\JsonEntity;
use dicr\validate\ValidateException;
use Yii;
use yii\base\Exception;

use function array_filter;

/**
 * Запрос результата решения капчи.
 *
 * @link https://rucaptcha.com/api-rucaptcha
 */
class ResultRequest extends JsonEntity
{
    /** @var string получить ответ на капчу */
    public const ACTION_GET = 'get';

    /** @var string получить стоимость решения отправленной капчи и ответ на нее */
    public const ACTION_GET2 = 'get2';

    /** @var string сообщить о верном ответе */
    public const ACTION_REPORT_GOOD = 'reportgood';

    /** @var string сообщить о неверном ответе */
    public const ACTION_REPORT_BAD = 'reportbad';

    /** @var string зарегистрировать новый callback-URL */
    public const ACTION_ADD_PINGBACK = 'add_pingback';

    /** @var string получить список зарегистрированных callback-URL */
    public const ACTION_GET_PINGBACK = 'get_pingback';

    /** @var string удалить callback-URL */
    public const ACTION_DEL_PINGBACK = 'del_pingback';

    /** @var string получить баланс счета */
    public const ACTION_GET_BALANCE = 'getbalance';

    /** @var string функция запроса (ACTION_*) */
    public $action;

    /**
     * @var ?string url для операций по добавлению/удалению callback-URL.
     * Вы можете использовать значение all совместно с del_pingback для удаления всех URL.
     */
    public $addr;

    /** @var int ID задачи решения капчи */
    public $id;

    /** @var int[] запрос результатов нескольких задач (id разделены запятыми) */
    public $ids;

    /** @var AntiCaptchaSimpleModule */
    private $module;

    /**
     * ResultRequest constructor.
     *
     * @param AntiCaptchaSimpleModule $module
     * @param array $config
     */
    public function __construct(AntiCaptchaSimpleModule $module, array $config = [])
    {
        $this->module = $module;

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function rules() : array
    {
        return [
            ['action', 'required'],
            ['action', 'string'],

            ['addr', 'trim'],
            ['addr', 'default'],
            ['addr', 'url'],

            ['id', 'default'],
            ['id', 'integer', 'min' => 1],
            ['id', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['ids', 'default']
        ];
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function getJson() : array
    {
        return array_filter([
            'action' => $this->action,
            'addr' => $this->addr,
            'id' => $this->id,
            'ids' => implode(',', $this->ids ?: []) ?: null,
        ]);
    }

    /**
     * Отправка запроса результата решения.
     *
     * @return Response
     * @throws Exception
     */
    public function send() : Response
    {
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        $data = array_filter(array_merge([
            'key' => $this->module->key,
            'json' => (int)(bool)$this->module->json,
            'header_acao' => (int)(bool)$this->module->headerAcao
        ], $this->json), static function ($val) : bool {
            return $val !== null && $val !== '';
        });

        $req = $this->module->httpClient->get('res.php', $data);

        Yii::debug('Запрос: ' . $req->toString(), __METHOD__);
        $res = $req->send();
        Yii::debug('Ответ: ' . $res->toString(), __METHOD__);

        if (! $res->isOk) {
            throw new Exception('HTTP-error: ' . $res->statusCode);
        }

        return Response::fromResponse($res);
    }
}
