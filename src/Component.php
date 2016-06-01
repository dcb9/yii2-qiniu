<?php

namespace dcb9\qiniu;

use League\Flysystem\AdapterInterface;
use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;
use Yii;
use yii\base\InvalidConfigException;

/**
 * Class Component
 *
 * @package dcb9\qiniu
 */
class Component extends \yii\base\Component
{
    public $accessKey;
    public $secretKey;

    private $_disks;

    /**
     * @var Auth
     */
    private $_auth;

    public function init()
    {
        parent::init();

        if (!$this->accessKey) {
            throw new InvalidConfigException('accessKey can not be blank');
        }

        if (!$this->secretKey) {
            throw new InvalidConfigException('secretKey can not be blank');
        }
    }

    protected function getAuth()
    {
        if (!$this->_auth) {
            $this->_auth = new Auth($this->accessKey, $this->secretKey);
        }

        return $this->_auth;
    }

    /**
     * @param array $disks
     */
    public function setDisks(array $disks)
    {
        foreach ($disks as $id => $component) {
            $this->setDisk($id, $component);
        }
    }

    public function setDisk($id, array $definition)
    {
        if (!isset($definition['class'])) {
            $definition['class'] = QiniuAdapter::className();
        }
        /* @var $qiniuAdapter QiniuAdapter */
        $qiniuAdapter = Yii::createObject($definition);
        $qiniuAdapter->setAuth($this->getAuth());

        $this->_disks[$id] = $this->createFilesystem($qiniuAdapter);
    }

    /**
     * @param $id
     * @return Filesystem
     */
    public function getDisk($id)
    {
        if (!isset($this->_disks[$id])) {
            throw new \BadMethodCallException('Unknown disk id: ' . $id);
        }

        return $this->_disks[$id];
    }

    protected function createFilesystem(AdapterInterface $adapter, array $config = null)
    {
        return new Filesystem($adapter, $config);
    }

    /**
     * @param string $bucket
     * @param null $key
     * @param int $expires
     * @param null $policy
     * @param bool $strictPolicy
     * @return string
     */
    public function getUploadToken($bucket, $key = null, $expires = 3600, $policy = null, $strictPolicy = true)
    {
        return $this->getAuth()
            ->uploadToken($bucket, $key, $expires, $policy, $strictPolicy);
    }

    /**
     * @param string $bucket
     * @param null|string $pipeline
     * @param null|string $notifyUrl
     * @param bool $force
     *
     * @return PersistentFop
     */
    public function getPersistentFop($bucket, $pipeline = null, $notifyUrl = null, $force = false)
    {
        return new PersistentFop($this->_auth, $bucket, $pipeline, $notifyUrl, $force);
    }
}
