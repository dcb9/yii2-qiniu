<?php

return [
    'id' => 'tests',
    'basePath' => dirname(__DIR__),
    'components' => [
        'qiniu' => [
            'class' => 'dcb9\qiniu\Component',
            'accessKey' => 'R4-d3lnAvzmfID4sSFxJ6gOyNBoyH50nASCfWGHJ',
            'secretKey' => 'A1jlLqqFQbhUSnQbvyrirxzm7hbDgeIHbpyJo6lx',
            'disks' => [
                'testBucket' => [
                    'bucket' => 'testbucket',
                    'baseUrl' => 'http://o81q3p7bv.bkt.clouddn.com',
                    'isPrivate' => true,
                    'zone' => 'zone1',
                ],
            ],
        ],
    ],
];
