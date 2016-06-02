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
        $this->checkUrlExists($disk->getUrl($ret['key']));
    }

    public function testWriteStreamWithoutKey()
    {
        $disk = Yii::$app->qiniu->getDisk('privateBucket');
        list($ret, $err) = $disk->writeStreamWithoutKey(fopen(__DIR__ . '/yii2-logo.png', 'rb'));
        $this->assertTrue($err === null);
        $this->assertArrayHasKey('hash', $ret);
        $this->assertArrayHasKey('key', $ret);
        $this->checkUrlExists($disk->getUrl($ret['key']));
    }

    protected function checkUrlExists($url)
    {
        $headers = get_headers($url, 1);
        $this->assertTrue((bool)preg_match('/[2|3]0\d/', $headers[0]));
    }
}
