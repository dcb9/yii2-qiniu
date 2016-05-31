<?php

use dcb9\qiniu\Component;
use League\Flysystem\AdapterInterface;

class QiniuAdapterTest extends PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        $disk = Yii::$app->qiniu
            ->getDisk('testBucket');
        if ($disk->has('hello.txt')) {
            $disk->delete('hello.txt');
        }
        if ($disk->has('hello3.txt')) {
            $disk->delete('hello3.txt');
        }
        if ($disk->has('hello4.txt')) {
            $disk->delete('hello4.txt');
        }
    }

    public static function tearDownAfterClass()
    {
        $disk = Yii::$app->qiniu
            ->getDisk('testBucket');

        if ($disk->has('hello.txt')) {
            $disk->delete('hello.txt');
        }
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testEmptyBucket()
    {
        /* @var $component Component */
        Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '111',
            'secretKey' => '222',
            'disks' => [
                'testBucket' => [
                    'baseUrl' => 'http://xxx.xx.clouddn.com',
                ]
            ],
        ]);
    }

    /**
     * @expectedException \yii\base\InvalidConfigException
     */
    public function testEmptyBaseUrl()
    {
        /* @var $component Component */
        Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '111',
            'secretKey' => '222',
            'disks' => [
                'testBucket' => [
                    'bucket' => 'testBucket',
                ]
            ],
        ]);
    }

    public function testWrite()
    {
        $disk = Yii::$app->qiniu->getDisk('testBucket');

        $this->assertTrue($disk->write('hello.txt', 'hello world'));
        $this->assertTrue($disk->write('/testdir/hello.txt', 'hello world'));
    }

    /**
     * @depends testWrite
     */
    public function testGetSize()
    {
        $this->assertEquals(11, Yii::$app->qiniu->getDisk('testBucket')->getSize('/hello.txt'));
    }

    /**
     * @depends testWrite
     */
    public function testListContents()
    {
        $list = Yii::$app->qiniu->getDisk('testBucket')->listContents('/', true);
        $list = array_map(function ($item) {
            return $item['path'];
        }, $list);

        $this->assertTrue(in_array('hello.txt', $list));

        $this->assertEquals([], Yii::$app->qiniu->getDisk('testBucket')->listContents('/not-exist-dir', true));
    }

    /**
     * @depends testWrite
     */
    public function testRead()
    {
        $content = Yii::$app->qiniu->getDisk('testBucket')
            ->read('hello.txt');
        $this->assertEquals('hello world', $content);
    }

    /**
     * @depends testWrite
     * @expectedException \League\Flysystem\FileNotFoundException
     */
    public function testReadNotExistFile()
    {
        $this->assertFalse(Yii::$app->qiniu->getDisk('testBucket')
            ->read('not-exist-file.txt'));
    }

    /**
     * @depends testWrite
     */
    public function testUpdate()
    {
        $this->assertTrue(Yii::$app->qiniu->getDisk('testBucket')
            ->update('hello.txt', 'hello everyone'));
    }

    /**
     * @depends testUpdate
     */
    public function testGetSizeAfterUpdate()
    {
        $this->assertEquals(14, Yii::$app->qiniu->getDisk('testBucket')->getSize('/hello.txt'));
    }

    /**
     * @depends testWrite
     */
    public function testPut()
    {
        $disk = Yii::$app->qiniu
            ->getDisk('testBucket');

        $this->assertTrue($disk->put('hello.txt', 'update content'));
        $this->assertTrue($disk->put('hello2.txt', 'add new file'));
        $this->assertTrue($disk->has('hello2.txt'));
    }

    /**
     * @depends testPut
     */
    public function testRename()
    {
        $disk = Yii::$app->qiniu
            ->getDisk('testBucket');

        $this->assertTrue($disk->rename('hello2.txt', 'hello3.txt'));
        $this->assertFalse($disk->has('hello2.txt'));
        $this->assertTrue($disk->has('hello3.txt'));
    }

    /**
     * @depends testRename
     */
    public function testCopy()
    {
        $disk = Yii::$app->qiniu
            ->getDisk('testBucket');
        $disk->copy('hello3.txt', 'hello4.txt');
        $this->assertTrue($disk->has('hello3.txt') && $disk->has('hello4.txt'));
        $this->assertEquals($disk->read('hello3.txt'), $disk->read('hello4.txt'));
    }

    /**
     * @depends testCopy
     */
    public function testReadAndDelete()
    {
        $disk = Yii::$app->qiniu
            ->getDisk('testBucket');

        $content = $disk->readAndDelete('hello3.txt');
        $content2 = $disk->readAndDelete('hello4.txt');

        $this->assertEquals($content, $content2);
        $this->assertFalse($disk->has('hello3.txt') && $disk->has('hello4.txt'));
    }

    public function testDeleteDir()
    {
        $disk = Yii::$app->qiniu
            ->getDisk('testBucket');
        $this->assertTrue($disk->deleteDir('/testdir'));
        $this->assertTrue($disk->deleteDir('/testdir2'));
    }

    public function testCreateDir()
    {
        $disk = Yii::$app->qiniu
            ->getDisk('testBucket');
        $disk->createDir('/testdir');
    }

    /**
     * @dataProvider setVisibilityDataProvider
     */
    public function testSetVisibility($isPrivate)
    {
        $v = Yii::createObject([
            'class' => Component::className(),
            'accessKey' => '111',
            'secretKey' => '222',
            'disks' => [
                'testBucket' => [
                    'isPrivate' => $isPrivate,
                    'bucket' => 'testBucket',
                    'baseUrl' => 'http://xxx.xx.clouddn.com',
                ]
            ],
        ])->getDisk('testBucket')->setVisibility('/', AdapterInterface::VISIBILITY_PUBLIC);

        $this->assertTrue($v);
    }

    public function setVisibilityDataProvider()
    {
        return [[false], [true]];
    }

    public function testGetMimetype()
    {
        $mimetype = Yii::$app->qiniu
            ->getDisk('testBucket')
            ->getMimetype('/hello.txt');

        $this->assertEquals('text/plain', $mimetype);
    }

    public function testTimestamp()
    {
        $val = Yii::$app->qiniu
            ->getDisk('testBucket')
            ->getTimestamp('/hello.txt');

        $this->assertTrue(is_int($val));
        $this->assertTrue($val > 0);
    }

    public function testGetVisibility()
    {
        $val = Yii::$app->qiniu
            ->getDisk('testBucket')
            ->getVisibility('/hello.txt');

        $this->assertTrue(in_array($val, [
            AdapterInterface::VISIBILITY_PRIVATE,
            AdapterInterface::VISIBILITY_PUBLIC
        ]));
    }
}
