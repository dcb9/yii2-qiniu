<?php

namespace dcb9\qiniu;

use ArrayAccess;
use IteratorAggregate;
use yii\base\ArrayAccessTrait;

/**
 * 上传策略
 *
 * 上传策略是资源上传时附带的一组配置设定。通过这组配置信息，七牛云存储可以了解用户上传的需求：
 * 它将上传什么资源，上传到哪个空间，上传结果是回调通知还是使用重定向跳转，
 * 是否需要设置反馈信息的内容，以及授权上传的截止时间等等。
 * 上传策略同时还参与请求验证，可以验证用户对某个资源的上传请求是否完整。
 *
 * @package dcb9\qiniu
 * @see http://developer.qiniu.com/article/developer/security/put-policy.html
 *
 * @property string $scope Bucket 指定上传的目标资源空间 (Bucket) 和资源键 (Key)
 *   有两种格式：
 *     ● <bucket>，表示允许用户上传文件到指定的 bucket。
 *       在这种格式下文件只能“新增”，若已存在同名资源上传则会失败。
 *     ● <bucket>:<key>，表示只允许用户上传指定key的文件。
 *       在这种格式下文件默认允许“修改”，若已存在同名资源则会被覆盖。
 *       如果只希望上传指定key的文件，并且不允许修改，那么可以将下面的 insertOnly 属性值设为 1。
 *
 * @property int $deadline UnixTimestamp 上传凭证有效截止时间。
 *   Unix时间戳，单位：秒。该截止时间为上传完成后，在七牛空间生成文件的校验时间，
 *   而非上传的开始时间，一般建议设置为“上传开始时间+3600s”，
 *   用户可根据具体的业务场景对凭证截止时间进行调整。
 *
 * @property int $insertOnly AllowFileUpdating 限定为“新增”语意。
 *   如果设置为非0值，则无论scope设置为什么形式，仅能以“新增”模式上传文件。
 *
 * @property string $endUser EndUserId 唯一属主标识。
 *   特殊场景下非常有用，比如根据"App-Client"标识给图片或视频打水印。
 *
 * @property string $returnUrl RedirectURL Web端文件上传成功后，浏览器执行303跳转的URL。
 *   通常用于HTML Form上传。
 *   文件上传成功后会跳转到<returnUrl>?upload_ret=<queryString>, <queryString>包含returnBody内容。
 *   如不设置returnUrl，则直接将returnBody的内容返回给客户端。
 *
 * @property string $returnBody ResponseBodyForAppClient 上传成功后，自定义七牛云最终返回給上传端（在指定returnUrl时是携带在跳转路径参数中）的数据。
 *   支持魔法变量和自定义变量。
 *   returnBody 要求是合法的 JSON 文本。
 *   如：{"key": $(key), "hash": $(etag), "w": $(imageInfo.width), "h": $(imageInfo.height)}。
 * @see http://developer.qiniu.com/article/kodo/kodo-developer/up/vars.html#magicvar
 * @see http://developer.qiniu.com/article/kodo/kodo-developer/up/vars.html#xvar
 *
 * @property string $callbackUrl RequestUrlForAppServer 上传成功后，七牛云向"App-Server"发送POST请求的URL。
 *   必须是公网上可以正常进行POST请求并能响应"HTTP/1.1 200 OK"的有效URL。
 *   另外，为了给客户端有一致的体验，我们要求callbackUrl 返回包 Content-Type 为 "application/json"，即返回的内容必须是合法的 JSON 文本。
 *   出于高可用的考虑，本字段允许设置多个 callbackUrl(用 ; 分隔)，在前一个 callbackUrl 请求失败的时候会依次重试下一个callbackUrl。
 *   一个典型例子是 http://<ip1>/callback;http://<ip2>/callback，并同时指定下面的 callbackHost 字段。
 *   在 callbackUrl 中使用 ip 的好处是减少了对 dns 解析的依赖，可改善回调的性能和稳定性。
 *
 * @property string $callbackHost RequestHostForAppServer 上传成功后，七牛云向"App-Server"发送回调通知时的 Host 值。
 *   与callbackUrl配合使用，仅当设置了 callbackUrl 时才有效。
 *
 * @property string $callbackBody RequestBodyForAppServer 上传成功后，七牛云向"App-Server"发送Content-Type: application/x-www-form-urlencoded 的POST请求。
 *   该字段"App-Server"可以通过直接读取请求的query来获得，支持魔法变量和自定义变量。
 *   callbackBody 要求是合法的 url query string。如：key=$(key)&hash=$(etag)&w=$(imageInfo.width)&h=$(imageInfo.height)。
 *
 * @property string $callbackBodyType RequestBodyTypeForAppServer 上传成功后，七牛云向"App-Server"发送回调通知callbackBody的Content-Type。
 *   默认为application/x-www-form-urlencoded，也可设置为application/json。
 *
 * @property int $callbackFetchKey RequestKeyForApp 是否启用fetchKey上传模式。
 *   0为关闭，1为启用。具体见callbackFetchKey详解。
 * @see http://developer.qiniu.com/article/developer/security/put-policy.html#fetchkey
 *
 * @property string $persistentOps persistentOpsCmds 资源上传成功后触发执行的预转持久化处理指令列表。
 *   每个指令是一个API规格字符串，多个指令用“;”分隔。 请参看persistenOps详解与示例。
 *
 * @property string $persistentNotifyUrl persistentNotifyUrl 接收预转持久化结果通知的URL。
 *   必须是公网上可以正常进行POST请求并能响应"HTTP/1.1 200 OK"的有效URL。
 *   该URL获取的内容和持久化处理状态查询(prefop)的处理结果一致。
 *   发送body格式为Content-Type为application/json的POST请求，需要按照读取流的形式读取请求的body才能获取。
 *
 * @property string $persistentPipeline persistentPipeline 转码队列名。
 *   资源上传成功后，触发转码时指定独立的队列进行转码。为空则表示使用公用队列，处理速度比较慢。建议使用专用队列。
 *
 * @property string $saveKey SaveKey 自定义资源名。
 *   支持魔法变量及自定义变量。这个字段仅当用户上传的时候没有主动指定key的时候起作用。
 *
 * @property int $fsizeMin FileSizeMin 限定上传文件大小最小值，单位：字节（Byte）。
 * @property int $fsizeLimit FileSizeLimit 限定上传文件大小最大值，单位：字节（Byte）。
 *   超过限制上传文件大小的最大值会被判为上传失败，返回413状态码。
 *
 * @property int $detectMime AutoDetectMimeType 开启MimeType侦测功能。
 *   设为非0值，则忽略上传端传递的文件MimeType信息，使用七牛服务器侦测内容后的判断结果。
 *   默认设为0值，如上传端指定了MimeType则直接使用该值，否则按如下顺序侦测MimeType值：
 *     1. 检查文件扩展名；
 *     2. 检查Key扩展名；
 *     3. 侦测内容。
 *   如不能侦测出正确的值，会默认使用 application/octet-stream 。
 *
 * @property string $mimeLimit MimeLimit 限定用户上传的文件类型。
 *   指定本字段值，七牛服务器会侦测文件内容以判断MimeType，再用判断值跟指定值进行匹配，匹配成功则允许上传，匹配失败则返回403状态码。
 *   示例：
 *     ● image/*表示只允许上传图片类型
 *     ● image/jpeg;image/png表示只允许上传jpg和png类型的图片
 *     ● !application/json;text/plain表示禁止上传json文本和纯文本。注意最前面的感叹号！
 *
 * @property int $deleteAfterDays deleteAfterDays 文件在多少天后被删除
 *   七牛将文件上传时间与指定的deleteAfterDays天数相加，得到的时间入到后一天的午夜(CST,中国标准时间)，从而得到文件删除开始时间。
 *   例如文件在2015年1月1日上午10:00 CST上传，指定deleteAfterDays为3天，那么会在2015年1月5日00:00 CST之后当天内删除文件。
 */
class Policy implements IteratorAggregate, ArrayAccess
{
    use ArrayAccessTrait;

    protected $data;

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }

    public function __construct(array $policy = [])
    {
        $this->data = $policy;
    }

    public function __toArray()
    {
        return $this->data;
    }
}
