Qiniu Extension for Yii2
=================

The Qiniu integration for the Yii framework

[![Build Status](https://travis-ci.org/dcb9/yii2-qiniu.svg)](https://travis-ci.org/dcb9/yii2-qiniu)
[![Code Climate](https://codeclimate.com/github/dcb9/yii2-qiniu/badges/gpa.svg)](https://codeclimate.com/github/dcb9/yii2-qiniu)
[![Issue Count](https://codeclimate.com/github/dcb9/yii2-qiniu/badges/issue_count.svg)](https://codeclimate.com/github/dcb9/yii2-qiniu)
[![Latest Stable Version](https://poser.pugx.org/dcb9/yii2-qiniu/version)](https://packagist.org/packages/dcb9/yii2-qiniu)
[![Total Downloads](https://poser.pugx.org/dcb9/yii2-qiniu/downloads)](https://packagist.org/packages/dcb9/yii2-qiniu)
[![License](https://poser.pugx.org/dcb9/yii2-qiniu/license)](https://packagist.org/packages/dcb9/yii2-qiniu)

[CHANGE LOG](CHANGELOG.md)

Installation
--------------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist dcb9/yii2-qiniu
```

or add

```json
"dcb9/yii2-qiniu": "*"
```

to the `require` section of your composer.json.


Configuration
--------------------

To use this extension, simply add the following code in your application configuration:

```php
return [
    //....
    'components' => [
        'qiniu' => [
            'class' => 'dcb9\qiniu\Component',
            'accessKey' => 'YOUR_ACCESS_KEY',
            'secretKey' => 'YOUR_SECRET_KEY',
            'disks' => [
                'testBucket' => [
                    'bucket' => 'bucketOnQiniu',
                    'baseUrl' => 'ACCESS_QINIU_URL',
                    'isPrivate' => true,
                    'zone' => 'zone0', // 可设置为 zone0, zone1 @see \Qiniu\Zone
                ],
            ],
        ],
    ],
];
```

[资源操作](http://developer.qiniu.com/code/v6/api/kodo-api/index.html#资源操作)
--------------------

资源操作就 [Flysystem](https://github.com/thephpleague/flysystem) 的一个扩展, 所以所有的调用方法与 [Flysystem](https://github.com/thephpleague/flysystem) 调用方法一致.

```php
// 获取 Disk
$filesystem = Yii::$app->qiniu->getDisk('testBucket');

$filesystem->has('hello.txt');
```

**[所有可调用的 API](http://flysystem.thephpleague.com/api/)**

#### [上传策略](http://developer.qiniu.com/article/developer/security/put-policy.html)

默认设置 Policy 是使用 Array 的方式来设置的, 但是这种方式对程序员不是很友好,于是创建了一个 Policy 的类,但所有的操作还是跟操作数组一样.

```php
$policy = new \dcb9\qiniu\Policy();
$policy->callbackUrl = '';
$policy->callbackBody = '';
```

#### [获取 UploadToken](http://developer.qiniu.com/article/developer/security/upload-token.html)

```php
$qiniu = Yii::$app->qiniu;

$token1 = $qiniu->getUploadToken('testbucket');

$key = null;
$expires = 3600;
$policy = new \dcb9\qiniu\Policy();
$policy->callbackUrl = '';
$policy->callbackBody = '';

// Fop @see src/Pfop.php
$policy->persistentOps = \dcb9\qiniu\Pfop::instance()
    ->avthumb('mp4')
    ->wmImage('http://o82pobmde.bkt.clouddn.com/yii2-logo.png')
    ->saveas('testbucket', 'after-ops' . date('Y-m-d H:i:s') . '.mp4')
    ->__toString();
$policy->persistentNotifyUrl = 'http://blog.phpor.me';

$token2 = $qiniu->getUploadToken('testbucket', $key, $expires, $policy);
```

#### 使用 Token 上传文件

```php
$token = '<TOKEN>'; // @see 获取 UploadToken
$config = ['token' => $token];
$filesystem->writeStream($path, $stream, $config);

$filesystem->write($path, $content, $config);

$filesystem->put($path, $content, $config);
```

Tricks
--------------------

* 给配置的组件加 IDE 自动补全 [IDE autocompletion for custom components](https://github.com/samdark/yii2-cookbook/blob/master/book/ide-autocompletion.md)
