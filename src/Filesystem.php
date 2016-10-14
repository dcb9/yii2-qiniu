<?php

namespace dcb9\qiniu;

use League\Flysystem\Util;
use Qiniu\Processing\PersistentFop;
use Yii;

/**
 * Class Filesystem
 * @package dcb9\qiniu
 *
 * @method QiniuAdapter  getAdapter();
 * @property-read string $bucket
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

    public function getBucket()
    {
        return $this->getAdapter()->bucket;
    }

    public function __get($name)
    {
        if ($name === 'bucket') {
            return $this->getBucket();
        }
    }

    /**
     * 获取持久化处理类
     *
     * 该类用于主动触发异步持久化操作
     *
     * @param null|string $pipeline
     * @param null|string $notifyUrl
     * @param bool $force
     *
     * @return PersistentFop
     */
    public function getPersistentFop($pipeline = null, $notifyUrl = null, $force = false)
    {
        return new PersistentFop($this->getAdapter()->getAuth(), $this->getBucket(), $pipeline, $notifyUrl, $force);
    }

    /**
     * 第三方资源抓取
     *
     * @see http://developer.qiniu.com/code/v6/api/kodo-api/rs/fetch.html
     * @see BucketManager::fetch()
     * @param $url
     * @param null $key
     *
     * @return array
     */
    public function fetchUrl($url, $key = null)
    {
        return $this->getAdapter()->fetchUrl($url, $key);
    }
}
