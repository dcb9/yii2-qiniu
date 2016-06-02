<?php

namespace dcb9\qiniu;

use Qiniu\Zone;
use Yii;
use yii\base\Configurable;
use yii\base\InvalidConfigException;
use Qiniu\Auth;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\ResumeUploader;
use League\Flysystem\Util;
use League\Flysystem\Config;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\AbstractAdapter;
use Qiniu\Config as QiniuConfig;

/**
 * Class QiniuAdapter
 *
 * @package dcb9\qiniu
 */
class QiniuAdapter extends AbstractAdapter implements Configurable
{
    public static function className()
    {
        return get_called_class();
    }

    private $_zone;

    public function __construct($config = [])
    {
        if (!empty($config)) {
            Yii::configure($this, $config);
        }
        if ($this->bucket === null) {
            throw new InvalidConfigException('The "bucket" property must be set.');
        } elseif ($this->baseUrl === null) {
            throw new InvalidConfigException('The "baseUrl" property must be set.');
        }
        $zone = isset($config['zone']) ? $config['zone'] : 'zone0';
        $this->setZone($zone);
    }

    public function getZone()
    {
        if (!$this->_zone) {
            $this->setZone('zone0');
        }

        return $this->_zone;
    }

    public function setZone($zone)
    {
        if (!in_array($zone, ['zone1', 'zone0'])) {
            throw new InvalidConfigException('The "zone" property must be "zone1" OR "zone0"');
        }
        $this->_zone = call_user_func(['Qiniu\Zone', $zone]);
    }

    /**
     * @var string 七牛存储Bucket
     */
    public $bucket;
    /**
     * @var string 基本访问域名
     */
    public $baseUrl;
    /**
     * @var bool 是否私有空间, 默认公开空间
     */
    public $isPrivate = false;

    private $_auth;

    /**
     * @return \Qiniu\Auth
     */
    public function getAuth()
    {
        return $this->_auth;
    }

    /**
     * @param \Qiniu\Auth $auth
     */
    public function setAuth(Auth $auth)
    {
        $this->_auth = $auth;
    }

    private $_bucketManager;

    /**
     * @return \Qiniu\Storage\BucketManager
     */
    public function getBucketManager()
    {
        if ($this->_bucketManager === null) {
            $this->setBucketManager(new BucketManager($this->getAuth()));
        }

        return $this->_bucketManager;
    }

    /**
     * @param \Qiniu\Storage\BucketManager $bucketManager
     */
    public function setBucketManager(BucketManager $bucketManager)
    {
        $this->_bucketManager = $bucketManager;
    }

    private $_uploadManager;

    /**
     * @return \Qiniu\Storage\UploadManager
     */
    public function getUploadManager()
    {
        if ($this->_uploadManager === null) {
            $this->setUploadManager(new UploadManager());
        }

        return $this->_uploadManager;
    }

    /**
     * @param \Qiniu\Storage\UploadManager $uploadManager
     */
    public function setUploadManager(UploadManager $uploadManager)
    {
        $this->_uploadManager = $uploadManager;
    }

    /**
     * @param null $key
     * @param int $expires
     * @param null $policy
     * @param bool|true $strictPolicy
     * @return string
     */
    public function getUploadToken($key = null, $expires = 3600, $policy = null, $strictPolicy = true)
    {
        return $this->getAuth()->uploadToken($this->bucket, $key, $expires, $policy, $strictPolicy);
    }

    /**
     * @param $path
     * @param $resource
     * @param $size
     * @param string $type
     * @return array
     */
    protected function streamUpload(
        $path,
        $resource,
        $size,
        $type = 'application/octet-stream',
        QiniuConfig $config = null,
        $token = null
    ) {
        if ($config === null) {
            $config = new \Qiniu\Config($this->_zone);
        }
        if ($token === null) {
            $token = $this->getUploadToken();
        }
        $resumeUploader = new ResumeUploader($token, $path, $resource, $size, null, $type, $config);

        return $resumeUploader->upload();
    }

    /**
     * @param string $path
     * @param integer $expires 只有 Bucket 为 private 时候该值才有效
     * @return string
     */
    public function getUrl($path, $expires = 3600)
    {
        $keyEsc = str_replace("%2F", "/", rawurlencode($path));

        $baseUrl = rtrim($this->baseUrl, '/') . '/' . $keyEsc;
        if ($this->isPrivate) {
            return $this->getAuth()->privateDownloadUrl($baseUrl, $expires);
        }

        return $baseUrl;
    }

    /**
     * @param array $file
     * @return array
     */
    protected function normalizeData(array $file)
    {
        return [
            'type' => 'file',
            'path' => $file['key'],
            'size' => $file['fsize'],
            'mimetype' => $file['mimeType'],
            'visibility' => $this->isPrivate ? AdapterInterface::VISIBILITY_PRIVATE : AdapterInterface::VISIBILITY_PUBLIC,
            'timestamp' => (int)($file['putTime'] / 10000000) //Epoch 时间戳
        ];
    }

    /**
     * @param $directory
     * @param null $start
     * @return array
     */
    protected function listDirContents($directory, $start = null)
    {
        list($item, $start, $err) = $this->getBucketManager()->listFiles($this->bucket, $directory, $start);
        if ($err !== null) {
            return [];
        } elseif (!empty($start)) {
            $item = array_merge($item, $this->listDirContents($directory, $start));
        }

        return $item;
    }

    /**
     * @inheritdoc
     */
    public function has($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function read($path)
    {
        $resource = $this->readStream($path);
        $resource['contents'] = stream_get_contents($resource['stream']);
        fclose($resource['stream']);
        unset($resource['stream']);

        return $resource;
    }

    /**
     * @inheritdoc
     */
    public function readStream($path)
    {
        $url = $this->getAuth()->privateDownloadUrl($this->getUrl($path));
        $stream = fopen($url, 'r');
        if (!$stream) {
            return false;
        }

        return compact('stream', 'path');
    }

    /**
     * @inheritdoc
     */
    public function listContents($directory = '', $recursive = false)
    {
        $files = [];
        foreach ($this->listDirContents($directory) as $k => $file) {
            $pathInfo = pathinfo($file['key']);
            $files[] = array_merge($pathInfo, $this->normalizeData($file), [
                'type' => isset($pathInfo['extension']) ? 'file' : 'dir',
            ]);
        }

        return $files;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($path)
    {
        list($ret, $err) = $this->getBucketManager()->stat($this->bucket, $path);
        if ($err !== null) {
            return false;
        }
        $ret['key'] = $path;

        return $this->normalizeData($ret);
    }

    /**
     * @inheritdoc
     */
    public function getSize($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function getMimetype($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function getTimestamp($path)
    {
        return $this->getMetadata($path);
    }

    /**
     * @inheritdoc
     */
    public function getVisibility($path)
    {
        return [
            'visibility' => $this->isPrivate ? AdapterInterface::VISIBILITY_PRIVATE : AdapterInterface::VISIBILITY_PUBLIC
        ];
    }

    /**
     * @inheritdoc
     */
    public function write($path, $contents, Config $config)
    {
        $stream = fopen('php://temp', 'w+b');
        fwrite($stream, $contents);
        rewind($stream);
        $result = $this->writeStream($path, $stream, $config);
        is_resource($stream) && fclose($stream);

        if (is_array($result)) {
            $result['contents'] = $contents;
            $result['mimetype'] = Util::guessMimeType($path, $contents);
        }

        return $result;
    }

    private $_lastError;

    public function getLastError()
    {
        $lastError = $this->_lastError;
        $this->_lastError = null;

        return $lastError;
    }

    /**
     * @inheritdoc
     */
    public function writeStream($path, $resource, Config $config)
    {
        $size = Util::getStreamSize($resource);
        $token = $config->get('token');
        list(, $err) = $this->streamUpload($path, $resource, $size, 'application/octet-stream', null, $token);
        if ($err !== null) {
            $this->_lastError = $err;

            return false;
        }

        return compact('size', 'path');
    }

    /**
     * @inheritdoc
     */
    public function update($path, $contents, Config $config)
    {
        return $this->delete($path) && $this->write($path, $contents, $config);
    }

    /**
     * @inheritdoc
     */
    public function updateStream($path, $resource, Config $config)
    {
        return $this->writeStream($path, $resource, $config);
    }

    /**
     * @inheritdoc
     */
    public function rename($path, $newpath)
    {
        list(, $err) = $this->getBucketManager()->rename($this->bucket, $path, $newpath);

        return $err === null;
    }

    /**
     * @inheritdoc
     */
    public function copy($path, $newpath)
    {
        list(, $err) = $this->getBucketManager()
            ->copy($this->bucket, $path, $this->bucket, $newpath);

        return $err === null;
    }

    /**
     * @inheritdoc
     */
    public function delete($path)
    {
        list(, $err) = $this->getBucketManager()
            ->delete($this->bucket, $path);

        return $err === null;
    }

    /**
     * @inheritdoc
     */
    public function deleteDir($dirname)
    {
        // 七牛无目录概念. 目前实现方案是.列举指定目录资源.批量删除
        $keys = array_map(function ($file) {
            return $file['key'];
        }, $this->listDirContents($dirname));
        if (empty($keys)) {
            return true;
        }
        $batchDelete = BucketManager::buildBatchDelete($this->bucket, $keys);
        list(, $err) = $this->getBucketManager()
            ->batch($batchDelete);

        return $err === null;
    }

    /**
     * @inheritdoc
     */
    public function createDir($dirname, Config $config)
    {
        return ['path' => $dirname];
    }

    /**
     * @inheritdoc
     */
    public function setVisibility($path, $visibility)
    {
        if ($this->isPrivate) {
            $visibility = AdapterInterface::VISIBILITY_PRIVATE;
        } else {
            $visibility = AdapterInterface::VISIBILITY_PUBLIC;
        }

        return compact('visibility');
    }

    public function writeWithoutKey($contents, Config $config)
    {
        $uploadManager = new UploadManager();
        $token = $this->getUploadToken();

        return $uploadManager->put($token, null, $contents);
    }

    public function writeStreamWithoutKey($resource, Config $config)
    {
        $size = Util::getStreamSize($resource);
        $token = $config->get('token');

        return $this->streamUpload(null, $resource, $size, 'application/octet-stream', null, $token);
    }
}
