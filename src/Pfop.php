<?php

namespace dcb9\qiniu;

/**
 * Class Pfop
 * @package dcb9\qiniu
 *
 * @method $this avthumb() avthumb($value)
 * @method $this segtime() segtime($value)
 * @method $this ab() ab($value)
 * @method $this aq() aq($value)
 * @method $this ar() ar($value)
 * @method $this r() r($value)
 * @method $this vb() vb($value)
 * @method $this vcodec() vcodec($value)
 * @method $this acodec() acodec($value)
 * @method $this scodec() scodec($value)
 * @method $this subtitle() subtitle($value)
 * @method $this ss() ss($value)
 * @method $this t() t($value)
 * @method $this autoscale() autoscale($value)
 * @method $this stripmeta() stripmeta(bool $value)
 * @method $this rotate() rotate($value)
 * @method $this wmImage() wmImage($value)
 * @method $this wmGravity() wmGravity($value)
 * @method $this wmText() wmText($value)
 * @method $this wmGravityText() wmGravityText($value)
 * @method $this wmFont() wmFont($value)
 * @method $this wmFontColor() wmFontColor($value)
 * @method $this wmFontSize() wmFontSize($value)
 * @method $this writeXing() writeXing($value)
 * @method $this an() an(bool $value)
 * @method $this vn() vn(bool $value)
 * @method $this s() s($value)
 */
class Pfop
{
    private function __construct()
    {
    }

    public static function instance($config = [])
    {
        $obj = new self;
        foreach ($config as $name => $value) {
            if (!property_exists($obj, $name)) {
                throw new \InvalidArgumentException('Property ' . $name . ' does not exists');
            }
            $obj->$name = $obj->handleValue($name, $value);
        }

        return $obj;
    }

    protected $avthumb;
    protected $segtime;
    protected $ab;
    protected $aq;
    protected $ar;
    protected $r;
    protected $vb;
    protected $vcodec;
    protected $acodec;
    protected $scodec;
    protected $subtitle;
    protected $ss;
    protected $t;
    protected $autoscale;
    protected $aspect;
    protected $stripmeta;
    protected $rotate;
    protected $wmImage;
    protected $wmGravity;
    protected $wmText;
    protected $wmGravityText;
    protected $wmFont;
    protected $wmFontColor;
    protected $wmFontSize;
    protected $writeXing;
    protected $an;
    protected $vn;
    protected $s;
    protected $saveas;

    /**
     * @param $w
     * @param $h
     * @return $this
     */
    public function aspect($w, $h)
    {
        $this->aspect = $w . ':' . $h;

        return $this;
    }

    /**
     * @param string $bucket
     * @param string $key
     * @return $this
     */
    public function saveas($bucket, $key)
    {
        $this->saveas = \Qiniu\base64_urlSafeEncode($bucket . ':' . $key);

        return $this;
    }

    protected function handleValue($name, $value)
    {
        if (in_array($name, ['an', 'vn', 'stripmeta'])) {
            $value = $value ? 1 : 0;
        } elseif (in_array($name, ['wmImage', 'wmText', 'wmFont', 'wmFontColor'])) {
            $value = \Qiniu\base64_urlSafeEncode($value);
        } elseif ($name === 'rotate' && !in_array($value, ['90', '180', '270', 'auto'])) {
            throw new \InvalidArgumentException('Rotate can not be ' . $value);
        }

        return $value;
    }

    /**
     * @param $name
     * @param $arguments
     * @return $this
     */
    public function __call($name, $arguments)
    {
        if (!property_exists($this, $name)) {
            throw new \BadMethodCallException();
        }
        $this->$name = $this->handleValue($name, array_shift($arguments));

        return $this;
    }

    public function __toString()
    {
        $keys = array_filter(array_keys(get_object_vars($this)), function ($property) {
            return !in_array($property, ['saveas']) && $this->$property !== null;
        });

        return call_user_func_array([$this, 'getValue'], $keys)
        . ($this->saveas === null ? '' : '|saveas/' . $this->saveas);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        $values = array_map(function ($key) {
            return $key . '/' . $this->$key;
        }, func_get_args());

        return implode('/', $values);
    }
}
