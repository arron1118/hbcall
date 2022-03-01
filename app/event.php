<?php
// 事件定义文件
return [
    'bind'      => [
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],
        'CallHistory' => [\app\common\event\CallHistory::class],
        'Payment' => [\app\common\event\Payment::class],
    ],

    'subscribe' => [
    ],
];
