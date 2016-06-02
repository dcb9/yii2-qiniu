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

    /**
     * 获取访问地址
     *
     * @param string $path
     * @param integer $expires 只有 Bucket 为 private 时候该值才有效
     * @return string
     */
    public function getUrl($path, $expires = 3600)
    {
        return $this->getAdapter()->getUrl($path, $expires);
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
