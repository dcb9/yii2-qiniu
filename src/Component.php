<?php

namespace dcb9\qiniu;

use Qiniu\Auth;
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

        $this->_auth = new Auth($this->accessKey, $this->secretKey);
    }
}
