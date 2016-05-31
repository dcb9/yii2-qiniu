<?php

use dcb9\qiniu\Component;
use dcb9\qiniu\Filesystem;

class ComponentTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testEmptyAccessKey()
    {
        Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '',
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testEmptySecretKey()
    {
        Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '111',
            'secretKey' => '',
        ]);
    }

    public function testCreateComponent()
    {
        $component = Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '111',
            'secretKey' => '222',
        ]);

        $this->assertInstanceOf(Component::className(), $component);
    }

    public function testCreateComponentWithDisk()
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

        $this->assertInstanceOf(Component::className(), $component);
        $this->assertInstanceOf(Filesystem::className(), $component->getDisk('testBucket'));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testGetInvalidDisk()
    {
        Yii::$app->qiniu->getDisk('invalidDistId');
    }
}
