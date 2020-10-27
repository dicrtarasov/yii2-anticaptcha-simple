<?php
/*
 * @copyright 2019-2020 Dicr http://dicr.org
 * @author Igor A Tarasov <develop@dicr.org>
 * @license MIT
 * @version 28.10.20 01:29:35
 */

declare(strict_types = 1);
namespace dicr\anticaptcha\simple;

use dicr\helper\JsonEntity;
use yii\httpclient\Client;

use function preg_match;

/**
 * Ответ за запрос решения капчи.
 *
 * @link https://rucaptcha.com/api-rucaptcha
 */
class Response extends JsonEntity
{
    /** @var bool статус приема запроса на решение */
    public $status;

    /**
     * @var string|int|null результат запрошенной операции или ошибка (если статус = 0)
     * При status =
     */
    public $request;

    /**
     * Создает объект ответа из ответа HTTP.
     *
     * @param \yii\httpclient\Response $res
     * @return static
     */
    public static function fromResponse(\yii\httpclient\Response $res) : self
    {
        $response = new self();

        $matches = null;
        if ($res->format === Client::FORMAT_JSON) {
            $res->format = Client::FORMAT_JSON;
            $response->status = (int)$res->data['status'];
            $response->request = $res->data['request'] ?? null;
        } elseif (strncasecmp($res->content, 'ERROR', 5) === 0) {
            $response->status = 0;
            $response->request = $res->content;
        } elseif (preg_match('~^OK(?:\\|(.+))?$~ui', $res->content, $matches)) {
            $response->status = 1;
            $response->request = $matches[1];
        } else {
            // при getbalance возвращает только результат
            $response->status = 1;
            $response->request = $res->content;
        }

        return $response;
    }
}
