<?php

use dcb9\qiniu\Component;

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
}
