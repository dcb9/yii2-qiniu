<?php

/**
 * Yii bootstrap file.
 * Used for enhanced IDE code auto completion.
 */
class Yii extends \yii\BaseYii
{
    /**
     * @var BaseApplication|WebApplication|ConsoleApplication the application instance
     */
    public static $app;
}

/**
 * Class ConsoleApplication
 * Include only console application related components here
 *
 * @property \dcb9\qiniu\Component $qiniu Qiniu component
 */
class ConsoleApplication extends yii\console\Application
{
}
