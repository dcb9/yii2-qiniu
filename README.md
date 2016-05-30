Qiniu Extension for Yii2
=================

The Qiniu integration for the Yii framework"

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
    ],
];
```

Tricks
--------------------

* 给配置的组件加 IDE 自动补全 [IDE autocompletion for custom components](https://github.com/samdark/yii2-cookbook/blob/master/book/ide-autocompletion.md)
