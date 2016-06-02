<?php

class QiniuAdapter2Test extends PHPUnit_Framework_TestCase
{
    public function testWriteWithoutKey()
    {
        $disk = Yii::$app->qiniu->getDisk('testBucket');
        list($ret, $err) = $disk->writeWithoutKey('hello from write without key');
        $this->assertTrue($err === null);
        $this->assertArrayHasKey('hash', $ret);
        $this->assertArrayHasKey('key', $ret);
    }

    public function testWriteStreamWithoutKey()
    {
        $disk = Yii::$app->qiniu->getDisk('testBucket');
        list($ret, $err) = $disk->writeStreamWithoutKey(fopen(__DIR__ . '/yii2-logo.png', 'rb'));
        $this->assertTrue($err === null);
        $this->assertArrayHasKey('hash', $ret);
        $this->assertArrayHasKey('key', $ret);
    }
}
