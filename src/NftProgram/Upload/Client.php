<?php
/*
 * This file is part of the nft_zxinchain
 * (c) moshong <9080@live.com>
 * Date:2022/4/18 3:22 下午
 * 
 */

namespace NftZxinchainn\NftProgram\Upload;
use NftZxinchainn\NftProgram\Auth\Client as BaseClient;
use NftZxinchainn\Tools\Exception;
use NftZxinchainn\Tools\ServiceContainer;
use NftZxinchainn\Tools\Support\File;
use NftZxinchainn\Tools\Support\Str;

class Client extends BaseClient
{

    /**
     * 生成素材上传临时密钥
     * (c) moshong <9080@live.com>
     * @param string $userPubKey 发行人公钥 | required
     * @param string $seriesName 系列名
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json
     * @return          string tempSecretId cos临时密钥id | string tempSecretKey cos临时密钥key | string sessionToken 请求时需要用的 token 字符串 | string uploadAddress cos地址
     */
    public function getSecret($userPubKey='',$seriesName=null){

        $pubKey = $this->app->config['pubKey'];
        $priKey = $this->app->config['priKey'];
        $userPubKey = $userPubKey?$userPubKey:$pubKey;
        $timestamp = $this->headers['Signature-Time'];
        $params = [
            'timestamp' => $timestamp,
            'pubKey' => $pubKey,
            'userPubKey' => $userPubKey,
        ];
        if ($seriesName)
        {
            $pubSignedData = $timestamp.'_'.$seriesName.'_'.$userPubKey;
            $userSignedData = $timestamp.'_'.$seriesName;
            $params['seriesName'] = $seriesName;
        }else{
            $pubSignedData = $timestamp.'_'.$userPubKey;
            $userSignedData = ''.$timestamp;
        }
        $params['pubSignedData'] = $this->signByPriKey($pubSignedData,$priKey)['signedData'];
        $params['userSignedData'] = $this->signByPriKey($userSignedData,$priKey)['signedData'];
        return  $this->httpPostJson('/api/v1/nft/upload/secret', $params);
    }

    /**
     * sdk-文件上传至COS--本地路径提交
     * (c) moshong <9080@live.com>
     * @param string $filePath 本地文件名，包含路径 | required
     * @param string $seriesName 发行人公钥 |
     * @param string $cosPath 文件访问地址，零时密钥接口返回uploadAddress + (自定义文件名称)|
     * @param string $tempSecretId 临时密钥接口返回tempSecretId|
     * @param string $tempSecretKey 临时密钥接口返回tempSecretKey|
     * @param string $sessionToken 请求时需要用的 token 字符串 |
     *
     * @return JSON  err 为空时成功，其他为失败
     */
    public function uploadToCos($filePath='',$seriesName='',$cosPath='',$tempSecretId='',$tempSecretKey='',$sessionToken='')
    {
        if (!$filePath){
            throw new Exception('filePath不可为空');
        }
        if(!$cosPath || !$tempSecretId || !$tempSecretKey || !$sessionToken){
            $secret = $this->getSecret('',$seriesName);
            if(!isset($secret["retCode"]) || $secret["retCode"] != 0){
                throw new Exception($secret["retMsg"]);
            }
            $filename = Str::random(16).'_'.time().File::getStreamExt($filePath);
            $tempSecretId = $secret["data"]["tempSecretId"];
            $tempSecretKey = $secret["data"]["tempSecretKey"];
            $sessionToken = $secret["data"]["sessionToken"];
            $cosPath = $secret["data"]["uploadAddress"].$filename;
        }
        $params = [
            'cosPath' => $cosPath,
            'tempSecretId' => $tempSecretId,
            'tempSecretKey' => $tempSecretKey,
            'sessionToken' => $sessionToken,
            'filePath' => $filePath,
        ];
        $res=$this->httpPostJson('http://127.0.0.1:30505/uploadToCos', $params);
        if ($res['err'] != ''){
            throw new Exception('上传素材失败:'.$res['err']);
        }
        $result = ['cosUrl'=>$cosPath,'err'=>'上传素材成功'];
        if($seriesName) $result['seriesName'] = $seriesName;
        return $result;
    }

    /**
     * sdk-文件上传至COS--form-data文件提交
     * (c) moshong <9080@live.com>
     * @param string $cosPath 文件访问地址，零时密钥接口返回uploadAddress + (自定义文件名称)| required
     * @param string $tempSecretId 临时密钥接口返回tempSecretId| required
     * @param string $tempSecretKey 临时密钥接口返回tempSecretKey| required
     * @param string $sessionToken 请求时需要用的 token 字符串 | required
     * @param string $name 指定在cos中的文件名，确保唯一不重复 | required
     * @param object $file 文件 | required
     * @return JSON  err 为空时成功，其他为失败
     */
    public function uploadFileToCos($file=null,$seriesName='',$cosPath='',$tempSecretId='',$tempSecretKey='',$sessionToken='')
    {
        if (!$file || !isset($file["file"])){
            throw new Exception('file对象不存在');
        }
        if(!$cosPath || !$tempSecretId || !$tempSecretKey || !$sessionToken){
            $secret = $this->getSecret('',$seriesName);
            if(!isset($secret["retCode"]) || $secret["retCode"] != 0){
                throw new Exception($secret["retMsg"]);
            }
            $tempSecretId = $secret["data"]["tempSecretId"];
            $tempSecretKey = $secret["data"]["tempSecretKey"];
            $sessionToken = $secret["data"]["sessionToken"];
            $cosPath = $secret["data"]["uploadAddress"];
        }
        $name = Str::random(16).'_'.time().File::getStreamExt($file["file"]["tmp_name"]);
        $params = [
            'cosPath' => $cosPath,
            'tempSecretId' => $tempSecretId,
            'tempSecretKey' => $tempSecretKey,
            'sessionToken' => $sessionToken,
            'name' => $name,
        ];

        $res = $this->httpUpload('http://127.0.0.1:30505/uploadFileToCos', $file,$params);
        if ($res['err'] != ''){
            throw new Exception('上传素材失败:'.$res['err']);
        }
        $result = ['cosUrl'=>$cosPath.$name,'err'=>'上传素材成功'];
        if($seriesName) $result['seriesName'] = $seriesName;
        return $result;
    }

    /**
     * 图片内容检测
     * (c) moshong <9080@live.com>
     * @param string $imageUrl 素材网络地址（全路径） | required
     * @param int $interval 截帧频率，GIF图/长图检测专用，默认值为0，表示只会检测GIF图/长图的第一帧
     * @param int $maxFrames GIF图/长图检测专用，代表均匀最大截帧数量，默认值为1（即只取GIF第一张，或长图不做切分处理（可能会造成处理超时））。
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - string suggestion - 建议您拿到判断结果后的执行操作。建议值，Block：建议屏蔽，Review：建议复审，Pass：建议通过
     *                          string label - 恶意标签，Normal：正常，Porn：色情，Abuse：谩骂，Ad：广告。以及其他令人反感、不安全或不适宜的内容类型。
     *                          int score - 机器判断当前分类的置信度，取值范围：0~100。分数越高，表示越有可能属于当前分类。（如：色情 99，则该样本属于色情的置信度非常高。）
     */
    public function queryImageModeration($imageUrl=null,$interval=0,$maxFrames=1)
    {
        if (!$$imageUrl){
            throw new Exception('素材网络地址不能为空');
        }
        $params = [
            'imageUrl' => $imageUrl,
            'interval' => $interval,
            'maxFrames' => $maxFrames,
        ];
        return  $this->httpPostJson('/api/v1/nft/query/image/moderation',$params);
    }

}