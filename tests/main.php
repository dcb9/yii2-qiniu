<?php

return [
    'id' => 'tests',
    'basePath' => dirname(__DIR__),
    'components' => [
        'qiniu' => [
            'class' => 'dcb9\qiniu\Component',
            'accessKey' => '0nOV0Qe99aHk3BjI6Eak-1y2IAYfGMyG6756X2BB',
            'secretKey' => 'SSQpx6V7TDUnZBobJQiYdsjgGWH8WHOJP4M6gZtX',
            'disks' => [
                'testBucket' => [
                    'bucket' => 'testbucket',
                    'baseUrl' => 'http://o82y2yum4.bkt.clouddn.com',
                    'isPrivate' => false,
                    'zone' => 'zone0',
                ],
            ],
        ],
    ],
];
