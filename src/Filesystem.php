<?php

namespace dcb9\qiniu;

use League\Flysystem\Util;
use Yii;

/**
 * Class Filesystem
 * @package dcb9\qiniu
 *
 * @method QiniuAdapter  getAdapter();
 */
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
        return $this->getAdapter()->getUrl($path);
    }

    public function writeWithoutKey($contents, array $config = [])
    {
        $config = $this->prepareConfig($config);

        return $this->getAdapter()->writeWithoutKey($contents, $config);
    }

    public function writeStreamWithoutKey($resource, array $config = [])
    {
        $config = $this->prepareConfig($config);

        return $this->getAdapter()->writeStreamWithoutKey($resource, $config);
    }
}
