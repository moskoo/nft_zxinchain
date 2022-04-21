# 至信链NFT平台phpSDK

适用于至信链 NFT 平台接口-v0.0.2和钱包SDK本地服务

[SDK使用文档](https://rattler.cn)   [至信链浏览器](https://zxscan.qq.com)  [至信链官网](https://www.zxinchain.com)

![](https://img.shields.io/badge/MySQL-5.6%2B-brightgreen.svg?style=flat#crop=0&crop=0&crop=1&crop=1&id=ebVxY&originHeight=20&originWidth=86&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=jYpxH&originHeight=20&originWidth=86&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=OveOV&originHeight=20&originWidth=86&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=)   ![](https://img.shields.io/badge/PHP-7.3%2B-brightgreen.svg?style=flat#crop=0&crop=0&crop=1&crop=1&id=KRRxL&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=a2pg2&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=UCaqZ&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=)    ![](https://img.shields.io/badge/ZxNtf-0.02-brightgreen.svg?style=flat#crop=0&crop=0&crop=1&crop=1&id=KRRxL&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=ZCmgF&originHeight=20&originWidth=76&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=mCGZl&originHeight=20&originWidth=76&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=)

![](https://img.shields.io/badge/PHP-cURL-blue.svg?style=flat#crop=0&crop=0&crop=1&crop=1&id=KRRxL&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=ZCmgF&originHeight=20&originWidth=76&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=h6Jok&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=)   ![](https://img.shields.io/badge/PHP-OpenSSL-blue.svg?style=flat#crop=0&crop=0&crop=1&crop=1&id=KRRxL&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=ZCmgF&originHeight=20&originWidth=76&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=yP6RI&originHeight=20&originWidth=92&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=)   ![](https://img.shields.io/badge/PHP-SimpleXML-blue.svg?style=flat#crop=0&crop=0&crop=1&crop=1&id=KRRxL&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=ZCmgF&originHeight=20&originWidth=76&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=GUuwr&originHeight=20&originWidth=102&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=)   ![](https://img.shields.io/badge/PHP-fileinfo-blue.svg?style=flat#crop=0&crop=0&crop=1&crop=1&id=KRRxL&originHeight=20&originWidth=68&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=ZCmgF&originHeight=20&originWidth=76&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=#crop=0&crop=0&crop=1&crop=1&id=hAUNu&originHeight=20&originWidth=78&originalType=binary&ratio=1&rotation=0&showTitle=false&status=done&style=none&title=)

---

### 安装

```nginx
composer require moshong/nft_zxinchainn
```

---

**简单说几句废话：**

这是一个开源的非官方 SDK。安装非常简单，因为它是一个标准的 [Composer](https://getcomposer.org/) 包，这意味着任何满足下列安装条件的 PHP 项目支持 Composer 都可以使用它。该包目前只为满足至信链接口的php项目基本调用使用，如果你使用的是go语言～那么恭喜你，你可以直接使用官方的go sdk了～本sdk需要基于至信链本地钱包nft_wallet_service正常使用。至信链目前也是较小众商用型的ntf支持链了～需要的朋友不多，纯粹是为了方便自己，有啥纰漏请多担待～

### 使用示例

#### 初始化

```php
use NftZxinchainn\Factory;

$config = [
        'appId' => "",//应用appId
        'appKey' => "",//应用密钥
        'priKey' => "",//平台私钥
        'pubKey' => "",//平台公钥
        'identification' => "",//平台唯一用户识别号
        'test' => true //是否测试环境
];
$app = Factory::nftProgram($config);
```

#### 平台首次绑定激活

```php
$app->wallet->getVerifyCode('平台管理员身份证号',1,1);//获取手机验证码
$app->wallet->getUserInfo('平台管理员身份证号','验证码',1);//获取平台唯一标识，填入$config参数identification
$app->wallet->getVerifyCode('平台管理员身份证号',1,2);//获取邮件验证码
$app->auth->deriveKeyPair();//生成公私钥对 填入$config参数pubKey和priKey
$app->wallet->bindPlatformSelf('邮箱验证码');//平台自身绑定
$app->wallet->queryBindInfo('上一步返回的地址');//检查是否绑定成功
```

### 开源许可

使用 [Apache License 2.0](/LICENSE)，对公司、团队、个人等商用、非商用都免费开源。

# **数字藏品格式标准定义**
在发行数字藏品的时候，用户在定义下面4个字段的时候，需要关注格式问题：
displayUrl 预览图url
hash 藏品url内容对应hash
metaData 可选，藏品平台用户自定义内容
url  藏品介质url

建议格式：

1. displayUrl为藏品预览图，必须是图片（jpg、jpeg、png、gif、svg）且画面比例为1:1。
1. hash  藏品url内容对应hash， hash计算方法是用SM3
1. metaData 藏平用户自定义内容，供平台自己使用
1. url 藏品介质url
   1. 藏品介质是单个文件

url内容为藏品具体内容，推荐大小为50MB以内， 存放在至信链cos中。
为了方便藏品在各个平台进行转移，文件格式有一定要求，具体参看第5点：

1. 藏品介质是多个文件

url内容为json格式，存放在至信链cos中
```json
{
  "files": [
    { "type": 文件类型 , "url": "http://xxxx", "hash":"xxxxx" },
    { "type": 文件类型, "url": " http://xxxx", "hash":"xxxxx" }
  ],
  "attributes": [
    {
    "trait_type": "xxxx",
    "value": "xxxx"
    },
    {
    "trait_type": "xxx",
    "value": "xxxx"
    }
  ],
  "extensions": xxxx
}
```
files为必填字断，包含：
type: 文件类型,1-图片，2-音频，3-视频，4-3D模型 5-文本
url: 包含文件的url， url建议是至信链中存放文件的cos地址 （文件格式要求参照第5点）
hash 文件SM3值
attributes为可选字段： 包含
trait_type 藏品属性类型
value 属性类型对应的值
extensions为可选字段，用户自定义

1. 文件格式要求如下：
| 元商品类型 | 介质文件可选格式 | 备注 |
| --- | --- | --- |
| 图片 | jpg、jpeg、png、gif、svg |  |
| 音频 | mp3、wma、flac | 优先使用mp3格式 |
| 视频 | mp4、flv、wmv、mov | 优先使用mp4格式 |
| 3D模型 | glb、fbx、obj | 优先使用glb格式
若业务特殊需要，使用了fbx、obj格式，请业务方自行处理 glb->fbx & obj 格式的转换 |
| 文本 | txt |  |



