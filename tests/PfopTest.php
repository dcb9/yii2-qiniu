<?php

use dcb9\qiniu\Pfop;

class PfopTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider inInstanceProvider
     */
    public function testBuilderInInstance(array $config, $expect)
    {
        $this->assertEquals($expect, Pfop::instance($config)->__toString());
    }

    /**
     * @dataProvider builderDataProvider
     */
    public function testBuilder($actual, $expected)
    {
        $this->assertEquals($actual, $expected);
    }

    /**
     * @dataProvider builderBoolDataProvider
     */
    public function testBuilderBool($actual, $expected)
    {
        $this->assertEquals($actual, $expected);
    }

    /**
     * @dataProvider builderBase64DataProvider
     */
    public function testBuilderBase64($actual, $expected)
    {
        $this->assertEquals($actual, $expected);
    }

    public function builderBase64DataProvider()
    {
        return [
            [
                Pfop::instance()
                    ->wmImage('http://developer.qiniu.com/img/developer-logo@2x.png'),
                'wmImage/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vaW1nL2RldmVsb3Blci1sb2dvQDJ4LnBuZw==',
            ],
            [
                Pfop::instance()
                    ->wmFontColor('#FFFFFF'),
                'wmFontColor/I0ZGRkZGRg==',
            ],
            [
                Pfop::instance()
                    ->wmText('七牛视频处理'),
                'wmText/5LiD54mb6KeG6aKR5aSE55CG',
            ],
        ];
    }

    public function builderBoolDataProvider()
    {
        return [
            [
                Pfop::instance()
                    ->avthumb('mp4')
                    ->stripmeta(false)
                    ->an(false)
                    ->vn(true),
                'avthumb/mp4/stripmeta/0/an/0/vn/1',
            ],
            [
                Pfop::instance()
                    ->avthumb('mp4')
                    ->vn(false),
                'avthumb/mp4/vn/0',
            ],
            [
                Pfop::instance()
                    ->avthumb('mp4')
                    ->an('asdfasdf'),
                'avthumb/mp4/an/1',
            ],
        ];
    }

    public function builderDataProvider()
    {
        return [
            [
                Pfop::instance()
                    ->avthumb('m3u8')
                    ->segtime(10)
                    ->vcodec('libx264')
                    ->s('320x240')
                    ->__toString(),
                'avthumb/m3u8/segtime/10/vcodec/libx264/s/320x240',
            ],
            [
                Pfop::instance()
                    ->avthumb('mp4'),
                'avthumb/mp4',
            ],
            [
                Pfop::instance()
                    ->avthumb('mp4')
                    ->wmImage('http://developer.qiniu.com/img/developer-logo@2x.png'),
                'avthumb/mp4/wmImage/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vaW1nL2RldmVsb3Blci1sb2dvQDJ4LnBuZw=='
            ],
            [
                Pfop::instance()
                    ->avthumb('mp4')
                    ->wmImage('http://developer.qiniu.com/img/developer-logo@2x.png')
                    ->saveas('testBucket', 'testBucket.mp4'),
                'avthumb/mp4/wmImage/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vaW1nL2RldmVsb3Blci1sb2dvQDJ4LnBuZw==|saveas/dGVzdEJ1Y2tldDp0ZXN0QnVja2V0Lm1wNA==',
            ],
        ];
    }

    public function inInstanceProvider()
    {
        return [
            [
                [
                    'avthumb' => 'm3u8',
                    'segtime' => 10,
                    'vcodec' => 'libx264',
                    's' => '320x240',
                ],
                'avthumb/m3u8/segtime/10/vcodec/libx264/s/320x240',
            ],
            [
                ['avthumb' => 'mp4'],
                'avthumb/mp4',
            ],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentException()
    {
        Pfop::instance(['notExistProperty' => 1]);
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testBadMethodCallException()
    {
        Pfop::instance()->notExistFunc();
    }

    public function testSingleFunc()
    {
        $this->assertEquals('aspect/10:20', Pfop::instance()->aspect(10, 20));
        $this->assertEquals('|saveas/dGVzdEJ1Y2tldDp0ZXN0a2V5', Pfop::instance()->saveas('testBucket', 'testkey'));

        $this->assertEquals('an/1', Pfop::instance()->an(true));
        $this->assertEquals(
            'wmImage/aHR0cDovL2RldmVsb3Blci5xaW5pdS5jb20vaW1nL2RldmVsb3Blci1sb2dvQDJ4LnBuZw==',
            Pfop::instance()->wmImage('http://developer.qiniu.com/img/developer-logo@2x.png')
        );
        $this->assertEquals('rotate/90', Pfop::instance()->rotate(90));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidRotate()
    {
        Pfop::instance()->rotate(30);
    }
}
