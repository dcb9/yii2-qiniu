<?php

class QiniuAdapter2Test extends PHPUnit_Framework_TestCase
{
    public function testFetchUrl()
    {
        $disk = Yii::$app->qiniu->getDisk('testBucket');
        $avatar = 'https://wx.qlogo.cn/mmopen/VZDGic1IfDsiakiaQlgMvKzXPiaZafN2Z9TFI9HsFk8fLHd1NBnSs7DaibXP4nEIgM0y5Xo4frAD1N5Krm8Izrlpcdk4FndIt1TWY/0';
        list($ret, $err) = $disk->fetchUrl($avatar);
        $this->assertEquals(null, $err);
        $this->assertArrayHasKey('key', $ret);

        $avatar = 'http://error_url';
        list($ret, $err) = $disk->fetchUrl($avatar);
        $this->assertEquals(null, $ret);
        $this->assertInstanceOf('Qiniu\Http\Error', $err);


        $avatar = 'https://wx.qlogo.cn/mmopen/VZDGic1IfDsiakiaQlgMvKzXPiaZafN2Z9TFI9HsFk8fLHd1NBnSs7DaibXP4nEIgM0y5Xo4frAD1N5Krm8Izrlpcdk4FndIt1TWY/0';
        $key = 'test-avatar-certain-key';
        list($ret, $err) = $disk->fetchUrl($avatar, $key);
        $this->assertEquals(null, $err);
        $this->assertEquals($key, $ret['key']);
    }

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

    public function testGetPersistentFop()
    {
        $filesystem = Yii::$app->qiniu->getDisk('testBucket');
        $pfop = $filesystem->getPersistentFop();
        $this->assertInstanceOf('Qiniu\Processing\PersistentFop', $pfop);
    }
}
