# Клиент Simple API решения каптч для Yii2.

##### API: https://rucaptcha.com/api-rucaptcha

Простой протокол Rest-запросов поддерживается такими сервисами как:
- rucaptcha.com
- 2captcha.com
- pixodrom.com
- captcha24.com
- socialink.ru

## Настройка

```php
'modules' => [
    'anticaptcha' => [
        'class' => dicr\anticaptcha\simple\AntiCaptchaSimpleModule::class,
        'key' => 'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
    ]
]
```

## Использование

Запрос на решение простой текстовой капчи:
```php
// находим модуль
$module = Yii::$app->getModule('anticaptcha');

// создаем запрос
$req = $module->captchaRequest([
    'textCaptcha' => 'Привет'
]);

// отправляем
$res = $req->send();

// проверяем статус заявки
if (! $res->status) {
    throw new Exception('Ошибка: ' . $res->request);
}

// получаем id заявки
$id = (int)$res->request;
```

Получение решения:
```php
// запрос решения
$req = $module->resultRequest([
    'action' => ResultRequest::ACTION_GET,
    'id' => $id 
]);

// отправляем
$res = $res->send();

// проверяем статус заявки
if ($res->status) {
    echo 'Результат: ' . $res->request;
}
```
