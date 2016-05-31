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
}
