<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 28.10.20 01:28:45
 */

declare(strict_types = 1);
namespace dicr\anticaptcha\simple;

use Closure;
use dicr\helper\Url;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\httpclient\Client;

use function is_callable;

/**
 * Модуль AntiCaptcha Simple
 *
 * @property-read Client $httpClient
 * @link https://rucaptcha.com/api-rucaptcha
 */
class AntiCaptchaSimpleModule extends Module
{
    /** @var string сервис ruCaptcha */
    public const URL_RUCAPTCHA = 'https://rucaptcha.com';

    /** @var string сервис 2Captcha */
    public const URL_2CAPTCHA = 'https://2captcha.com';

    /** @var string сервис Pixodrom */
    public const URL_PIXODROM = 'http://pixodrom.com';

    /** @var string сервис Captcha24 */
    public const URL_CAPTCHA24 = 'http://captcha24.com';

    /** @var string сервис SocialLink */
    public const URL_SOCIALINK = 'https://www.socialink.ru';

    /** @var string URL сервиса API */
    public $url = self::URL_RUCAPTCHA;

    /** @var string ключ API */
    public $key;

    /** @var bool ответ в формате json (иначе текст) */
    public $json = true;

    /** @var bool Если включен, то in.php добавит заголовок Access-Control-Allow-Origin:* в ответ. */
    public $headerAcao = false;

    /**
     * @var ?int ID разработчика ПО. Разработчики, интегрировавшие свое ПО с нашим сервисом,
     * получают 10% от стоимости каждого такого запроса.
     */
    public $softId;

    /**
     * @var ?Closure обработчик callback-запросов с решениями function(int $task_id, string $result)
     * URL предварительно должен быть зарегистрирован на сервере.
     */
    public $handler;

    /** @inheritDoc */
    public $controllerNamespace = __NAMESPACE__;

    /**
     * @inheritDoc
     * @throws InvalidConfigException
     */
    public function init() : void
    {
        parent::init();

        if (empty($this->url)) {
            throw new InvalidConfigException('url');
        }

        if (empty($this->key)) {
            throw new InvalidConfigException('apiKey');
        }

        if (! empty($this->handler) && ! is_callable($this->handler)) {
            throw new InvalidConfigException('handler');
        }
    }

    /** @var Client */
    private $_httpClient;

    /**
     * HTTP-клиент.
     *
     * @return Client
     */
    public function getHttpClient() : Client
    {
        if ($this->_httpClient === null) {
            $this->_httpClient = new Client();
        }

        // переопределяем находу
        $this->_httpClient->baseUrl = $this->url;

        return $this->_httpClient;
    }

    /**
     * Возвращает callback-адрес.
     *
     * @return string
     */
    public function callbackUrl() : string
    {
        return Url::to($this->uniqueId . '/callback', true);
    }

    /**
     * Запрос создания задачи на решение капчи.
     *
     * @param array $config конфиг модели капчи (включая class)
     * @return CaptchaRequest
     */
    public function captchaRequest(array $config = []) : CaptchaRequest
    {
        return new CaptchaRequest($this, $config);
    }

    /**
     * Запрос результата решения капчи.
     *
     * @param array $config
     * @return ResultRequest
     */
    public function resultRequest(array $config = []) : ResultRequest
    {
        return new ResultRequest($this, $config);
    }
}
