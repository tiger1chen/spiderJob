<?php
return [
    'vendorPath' => dirname(dirname(__DIR__)) . '/vendor',
    'components' => [
        'cache' => [
           // 'class' => 'yii\caching\FileCache',
            'class' => 'yii\redis\Cache',
        ],
        'redis'=> [
        	'class'=>'yii\redis\Connection',
        	'hostname' => '10.10.10.10',
        	'port' => 6379,
        	'database' => 0,
        ],
    ],
];
