<?php

use dcb9\qiniu\Component;

class FilesystemTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \yii\base\ErrorException
     */
    public function testSetDiskWithInvalidConfig()
    {
        /* @var $component Component */
        $component = Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '111',
            'secretKey' => '222',
            'disks' => [
                'testBucket' => [
                    'bucket' => 'bucketOnQiniu',
                    'baseUrl' => 'http://xxx.xx.clouddn.com',
                ]
            ],
        ]);
        $component->setDisk('testBucket', '');
    }

    /**
     * @dataProvider urlDataProvider
     */
    public function testGetUrl($baseUrl, $path, $expect)
    {
        /* @var $component Component */
        $component = Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '111',
            'secretKey' => '222',
            'disks' => [
                'testBucket' => [
                    'bucket' => 'bucketOnQiniu',
                    'baseUrl' => $baseUrl,
                ]
            ],
        ]);
        $this->assertEquals($expect, $component->getDisk('testBucket')->getUrl($path));
    }

    public function urlDataProvider()
    {
        return [
            [
                'http://xxx.xx.clouddn.com/',
                'test.txt',
                'http://xxx.xx.clouddn.com/test.txt'
            ],
            [
                'http://xxx.xx.clouddn.com',
                'test.txt',
                'http://xxx.xx.clouddn.com/test.txt'
            ],
        ];
    }

    public function testGetBucket()
    {
        /* @var $component Component */
        $component = Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '111',
            'secretKey' => '222',
            'disks' => [
                'testBucket' => [
                    'bucket' => 'bucketOnQiniu',
                    'baseUrl' => 'http://xxx.xx.xx',
                ]
            ],
        ]);
        $this->assertEquals('bucketOnQiniu', $component->getDisk('testBucket')->bucket);
        $this->assertEquals('bucketOnQiniu', $component->getDisk('testBucket')->getBucket());
    }
}
