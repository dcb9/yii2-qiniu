<?php

use dcb9\qiniu\Policy;
use dcb9\qiniu\Pfop;

class PolicyTest extends PHPUnit_Framework_TestCase
{
    public static $path;

    public static function setUpBeforeClass()
    {
        static::$path = 'testmp4' . date('Y-m-d H:i:s') . '.mp4';
    }

    public function testBuilder()
    {
        $policy = new Policy();
        $this->assertEquals([], $policy->__toArray());

        $policy->callbackUrl = 'http://www.baidu.com';
        $this->assertEquals(['callbackUrl' => 'http://www.baidu.com'], $policy->__toArray());

        $policy->persistentOps = Pfop::instance()->avthumb('mp4')->saveas('testbucket', 'test.mp4');
        $this->assertEquals('avthumb/mp4|saveas/dGVzdGJ1Y2tldDp0ZXN0Lm1wNA==', (string)$policy['persistentOps']);
    }

    public function testWriteStreamAndPersistentFop()
    {
        $qiniu = Yii::$app->qiniu;
        $policy = new Policy();
        $policy->persistentOps = Pfop::instance()
            ->avthumb('mp4')
            ->wmImage('http://o82pobmde.bkt.clouddn.com/yii2-logo.png')
            ->saveas('testbucket', 'after-ops' . date('Y-m-d H:i:s') . '.mp4')
            ->__toString();

        $policy->persistentNotifyUrl = 'http://blog.phpor.me';

        $token = $qiniu->getUploadToken('testBucket', null, 3600, $policy);
        $disk = $qiniu->getDisk('testBucket');

        $filePath = __DIR__ . '/testmp4.mp4';
        $file = 'file://' . $filePath;
        if (!file_exists($filePath)) {
            $url = 'http://o82pobmde.bkt.clouddn.com/Son%20of%20Kama%20-%20The%20Surfing%20Piglet.mp4';
            $fp = fopen($file, 'w+');
            $ch = curl_init(str_replace(" ", "%20", $url));
            curl_setopt($ch, CURLOPT_TIMEOUT, 300);
            curl_setopt($ch, CURLOPT_FILE, $fp);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_exec($ch);
            curl_close($ch);
            fclose($fp);
        }
        $stream = fopen($file, 'rb');
        $config = ['token' => $token];
        $disk->writeStream(static::$path, $stream, $config);
    }

    public function testWriteWithToken()
    {
        $qiniu = Yii::$app->qiniu;
        $token = $qiniu->getUploadToken('testBucket');
        $disk = $qiniu->getDisk('testBucket');
        $this->assertTrue($disk->put('hello6.txt', 'hello6', ['token' => $token]));
        $this->assertEquals('hello6', $disk->read('hello6.txt'));
        $this->assertTrue($disk->delete('hello6.txt'));
    }

    /**
     * @depends testWriteStreamAndPersistentFop
     */
    public function testPfopAfterUpload()
    {
        $qiniu = Yii::$app->qiniu;
        $disk = $qiniu->getDisk('testBucket');
        $pfop = $disk->getPersistentFop();
        $fops = Pfop::instance()
            ->avthumb('flv')
            ->s('640x360')
            ->vb('1.25m')
            ->saveas($disk->getBucket(), 'testflv' . date('Y-m-d H:i:s') . '.flv')
            ->__toString();
        list(, $err) = $pfop->execute(static::$path, $fops);
        $this->assertTrue($err === null);
    }
}
