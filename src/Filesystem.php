<?php

namespace dcb9\qiniu;

use League\Flysystem\Util;
use Yii;

class Filesystem extends \League\Flysystem\Filesystem
{
    /**
     * Returns the fully qualified name of this class.
     * @return string the fully qualified name of this class.
     */
    public static function className()
    {
        return get_called_class();
    }

    public function getUrl($path)
    {
        /* @var $adapter QiniuAdapter */
        $adapter = $this->getAdapter();

        return $adapter->getUrl($path);
    }
}
