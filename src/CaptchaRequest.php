<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 28.10.20 01:29:22
 */

declare(strict_types = 1);
namespace dicr\anticaptcha\simple;

use dicr\helper\JsonEntity;
use dicr\validate\ValidateException;
use Yii;
use yii\base\Exception;

use function array_filter;

/**
 * Запрос решения капчи.
 *
 * @link https://rucaptcha.com/api-rucaptcha
 */
class CaptchaRequest extends JsonEntity
{
    /** @var string говорит о том, что вы отправляете изображение с помощью multipart-фомы */
    public const METHOD_POST = 'post';

    /** @var string говорит о том, что вы отправляете изображение в формате base64 */
    public const METHOD_BASE64 = 'base64';

    /** @var string для решения ReCaptcha */
    public const METHOD_USER_RECAPTCHA = 'userrecaptcha';

    /** @var string v3 — указывает на то, что это ReCaptcha V3 */
    public const VERSION_V3 = 'v3';

    /** @var int капча состоит только из цифр */
    public const NUMERIC_DIG = 1;

    /** @var int капча состоит только из букв */
    public const NUMERIC_LET = 2;

    /** @var int капча состоит либо только из букв, либо только из цифр */
    public const NUMERIC_OR = 3;

    /** @var int в капче могут быть и буквы, и цифры */
    public const NUMERIC_AND = 4;

    /** @var int капча содержит только кириллицу */
    public const LANGUAGE_CYR = 1;

    /** @var int капча содержит только латиницу */
    public const LANGUAGE_LAT = 2;

    /** @var string */
    public const PROXY_HTTP = 'HTTP';

    /** @var string */
    public const PROXY_HTTPS = 'HTTPS';

    /** @var string */
    public const PROXY_SOCKS4 = 'SOCKS4';

    /** @var string */
    public const PROXY_SOCKS5 = 'SOCKS5';

    /** @var string метод решения капчи */
    public $method;

    /** @var string (для ReCaptcha V3 используется значение "v3") */
    public $version;

    /** @var ?string файл изображения (для отправки методом method=post) */
    public $file;

    /** @var ?string содержимое файла в base64 (для отправки методом method=base64) */
    public $body;

    /** @var ?bool капча состоит из двух или более слов */
    public $phrase;

    /** @var ?bool капча чувствительна к регистру */
    public $regSense;

    /** @var ?int вариант наличия букв и цифр (NUMERIC_*) */
    public $numeric;

    /** @var ?bool требуется совершение математических действий (например: напишите результат 4 + 8 = ) */
    public $calc;

    /** @var ?int минимальное количество символов в ответе (1..20), по-умолчанию 0 - не определено */
    public $minLen;

    /** @var ?int максимальное кол-во символов в ответе (1..20), по-умолчанию 0 - не определено */
    public $maxLen;

    /** @var ?int какие символы в капче - латиница, кириллица (LANGUAGE_*) */
    public $language;

    /**
     * @var ?string язык интерфейса: en, ru, uk, ...
     * @link https://rucaptcha.com/api-rucaptcha#language
     */
    public $lang;

    /** @var ?string Текст капчи для текстовых капч. Например: Если завтра суббота, то какой сегодня день? */
    public $textCaptcha;

    /**
     * @var ?string Текст будет показан работнику, чтобы помочь ему правильно решить капчу. (140 симв.)
     * Например: введите только красные буквы.
     * Содержимое зависит от method, аналогично file
     */
    public $textInstructions;

    /**
     * @var ?string путь файла (method=post) или содержимое файла (при method=base64),
     * которое будет показано работнику, чтобы помочь ему решить капчу правильно.
     * Макс.: 450x150px, 100 Кбайт.
     */
    public $imgInstructions;

    /** @var ?string Значение параметра k или data-sitekey, которое вы нашли в коде страницы */
    public $googleKey;

    /** @var ?string Полный URL страницы, на которой вы решаете ReCaptcha V2 */
    public $pageUrl;

    /** @var ?bool невидимая reCaptcha 2 */
    public $invisible;

    /** @var ?string Значение параметра data-s найденное на странице. Актуально для поиска в Google и других сервисов Google. */
    public $dataS;

    /**
     * @var ?string Значение параметра action, которые вы нашли в коде сайта.
     * По умолчанию: verify
     */
    public $action;

    /**
     * @var float Требуемое значение рейтинга (score).
     * На текущий момент сложно получить токен со score выше 0.3.
     * По умолчанию: 0.4
     */
    public $minScore;

    /**
     * @var string[]|null ваши cookies которые будут использованы работником для решения капчи.
     * В ответе на капчу мы вернем cookies работника, но только при использовании json=1.
     */
    public $cookies;

    /** @var ?string Подставляем у работника ваш userAgent */
    public $userAgent;

    /** @var ?string тип прокси (PROXY_*) */
    public $proxyType;

    /** @var ?string адрес прокси логин:пароль@адрес:порт */
    public $proxy;

    /** @var AntiCaptchaSimpleModule */
    protected $module;

    /**
     * CaptchaRequest constructor.
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
    public function rules() : array
    {
        return [
            ['method', 'trim'],
            ['method', 'required'],

            ['version', 'trim'],
            ['version', 'default'],

            ['file', 'trim'],
            ['file', 'default'],

            ['body', 'trim'],
            ['body', 'default'],
            ['body', 'string', 'min' => 100, 'max' => 100 * 1024],

            ['phrase', 'default'],
            ['phrase', 'boolean'],
            ['phrase', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['regSense', 'default'],
            ['regSense', 'boolean'],
            ['regSense', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['numeric', 'default'],
            ['numeric', 'integer', 'min' => 0],
            ['numeric', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['calc', 'default'],
            ['calc', 'boolean'],
            ['calc', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            [['minLen', 'maxLen'], 'default'],
            [['minLen', 'maxLen'], 'integer', 'min' => 0, 'max' => 20],
            [['minLen', 'maxLen'], 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['language', 'default'],
            ['language', 'integer', 'min' => 0],
            ['language', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['lang', 'default'],
            ['lang', 'string', 'lang' => 2],

            ['textCaptcha', 'trim'],
            ['textCaptcha', 'default'],
            ['textCaptcha', 'string', 'max' => 140],

            ['textInstructions', 'trim'],
            ['textInstructions', 'default'],
            ['textInstructions', 'string', 'max' => 140],

            ['imgInstructions', 'trim'],
            ['imgInstructions', 'default'],
            ['imgInstructions', 'string', 'max' => 100 * 1024],

            ['googleKey', 'trim'],
            ['googleKey', 'default'],

            ['pageUrl', 'trim'],
            ['pageUrl', 'default'],

            ['invisible', 'default'],
            ['invisible', 'boolean'],
            ['invisible', 'filter', 'filter' => 'intval', 'skipOnEmpty' => true],

            ['dataS', 'trim'],
            ['dataS', 'default'],

            ['action', 'trim'],
            ['action', 'default'],

            ['minScore', 'default'],
            ['minScore', 'number', 'min' => 0, 'max' => 1],
            ['minScore', 'filter', 'filter' => 'floatval', 'skipOnEmpty' => true],

            ['cookies', 'default'],

            ['userAgent', 'trim'],
            ['userAgent', 'default'],

            ['proxyType', 'trim'],
            ['proxyType', 'default'],

            ['proxy', 'trim'],
            ['proxy', 'default'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function attributeFields() : array
    {
        // по-умолчанию не подменяем никакие поля
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getJson() : array
    {
        $cookies = [];
        foreach ($this->cookies ?: [] as $name => $val) {
            $cookies[] = $name . ':' . $val;
        }

        return [
            'method' => $this->method,
            'version' => $this->version,
            'body' => $this->body,
            'phrase' => $this->phrase,
            'regsense' => $this->regSense,
            'numeric' => $this->numeric,
            'calc' => $this->calc,
            'min_len' => $this->minLen,
            'max_len' => $this->maxLen,
            'language' => $this->language,
            'lang' => $this->lang,
            'textcaptcha' => $this->textCaptcha,
            'textinstructions' => $this->textInstructions,
            'imginstructions' => $this->imgInstructions,
            'googlekey' => $this->googleKey,
            'pageurl' => $this->pageUrl,
            'invisible' => $this->invisible,
            'data-s' => $this->dataS,
            'action' => $this->action,
            'min_score' => $this->minScore,
            'cookies' => implode(';', $cookies) ?: null,
            'userAgent' => $this->userAgent,
            'proxytype' => $this->proxyType,
            'proxy' => $this->proxy,
        ];
    }

    /**
     * Отправка запроса.
     *
     * @return Response
     * @throws Exception
     */
    public function send() : Response
    {
        // проверяем
        if (! $this->validate()) {
            throw new ValidateException($this);
        }

        // готовим данные запроса
        $data = array_filter(array_merge([
            'key' => $this->module->key,
            'header_acao' => (int)(bool)$this->module->headerAcao,
            'pingback' => $this->module->handler ? $this->module->callbackUrl() : null,
            'json' => (int)(bool)$this->module->json,
            'soft_id' => $this->module->softId
        ], $this->json), static function ($val) : bool {
            return $val !== null && $val !== '';
        });

        // запрос
        $req = $this->module->httpClient->post('in.php', $data);

        // добавляем файлы
        if ($this->method === self::METHOD_POST) {
            if (! empty($this->file)) {
                $req->addFile('file', $this->file);
            }

            if (! empty($this->imgInstructions)) {
                $req->addFile('imginstructions', $this->imgInstructions);
            }
        }

        // поехали
        Yii::debug('Запрос: ' . $req->toString(), __METHOD__);
        $res = $req->send();
        Yii::debug('Ответ: ' . $res->toString(), __METHOD__);

        if (! $res->isOk) {
            throw new Exception('HTTP-error: ' . $res->statusCode);
        }

        return Response::fromResponse($res);
    }
}
