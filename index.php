<?php

require_once __DIR__ . '/src/vendor/autoload.php';

use wh1te_w0lf\webhook_deploy\Deploy;

Deploy::fabric(file_exists(__DIR__ . '/config.json') ?
    json_decode(file_get_contents(__DIR__ . '/config.json'), true) :
    [
    'secret' => 'my-secret',
    'repository' => 'user/name',
    'log' => [
        'id' => 'log',
        'class' => 'wh1te_w0lf\\webhook_deploy\\FileLog',
        'logFileName' => __DIR__ . '/var/deploy.log',
    ],
    'notification' => [
        'class' => 'wh1te_w0lf\\webhook_deploy\\TelegramNotificator',
        'botKey' => 'Key',
        'chatId' => 'id',
        'id' => 'notification'
    ],
    'errorHandler' => [
        'class' => 'wh1te_w0lf\\webhook_deploy\\ErrorHandler',
        'log' => ['id' => 'log'],
        'notification' => ['id' => 'notification']
    ]
])->run($_GET, $_POST, $_SERVER);