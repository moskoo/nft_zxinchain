<?php
/*
 * This file is part of the nft_zxinchain
 * (c) moshong <9080@live.com>
 * Date:2022/4/22 9:02 上午
 * 
 */

namespace NftZxinchainn\NftProgram\NFT;

use NftZxinchainn\NftProgram\Auth\Client as BaseClient;
use NftZxinchainn\Tools\Exception;
use NftZxinchainn\Tools\ServiceContainer;
use NftZxinchainn\Tools\Support\File;
use NftZxinchainn\Tools\Support\Str;
use NftZxinchainn\Tools\Support\Bytes;

class Client extends BaseClient
{
    /**
     * 申请积分
     * (c) moshong <9080@live.com>
     * @param string $applyerAddr 申请对象地址 | required
     * @param int $count 积分数量 | required
     * @param string $operateId 请求ID,重复请求传上次id，新请求无需。 |
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function applyPoint($applyerAddr,$count=0,$operateId='')
    {
        if (!$applyerAddr){
            throw new Exception('申请对象地址不能为空');
        }
        if ($count<1){
            throw new Exception('积分数量必须大于0');
        }
        $operateId = $operateId?$operateId:$this->create_uuid();
        $platformPubKey = $this->app->config['pubKey'];
        $platformSignature  = $this->signByPriKey($platformPubKey.'_'.$applyerAddr.'_apply_point_'.$count.'_'.$operateId)['signedData'];
        $params = [
            'applyerAddr' => $applyerAddr,
            'platformPubKey' => $platformPubKey,
            'count' => $count,
            'operateId' => $operateId,
            'platformSignature' => $platformSignature,
        ];
        return  $this->httpPostJson('/api/v1/nft/point/apply', $params);
    }

    /**
     * 查询积分申请结果
     * (c) moshong <9080@live.com>
     * @param string $taskId 任务ID | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7, 任务失败:10 | string taskMsg 失败情况下会有提示信息 | string txHash 交易hash | int chainTimestamp 链上交易时间戳
     */
    public function getPointResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务ID不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'taskId' => $taskId,
            'platformPubKey' => $platformPubKey,
        ];
        return  $this->httpGet('/api/v1/nft/point/apply', $params);
    }

    /**
     * 积分销毁
     * (c) moshong <9080@live.com>
     * @param string $addr 销毁人的地址 | required
     * @param int $count 积分数量 | required
     * @param string $operateId 请求ID,重复请求传上次id，新请求无需。 |
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function destroyPoint($addr='',$count=0,$userPriKey='',$operateId='')
    {
        if (!$addr || !$userPriKey){
            throw new Exception('销毁人的地址或私钥不能为空');
        }
        if ($count<1){
            throw new Exception('积分数量必须大于0');
        }
        $operateId = $operateId?$operateId:$this->create_uuid();
        $platformPubKey = $this->app->config['pubKey'];
        $signature = $this->signByPriKey($platformPubKey.'_'.$addr.'_destroy_point_'.$count.'_'.$operateId,$userPriKey)['signedData'];
        $platformSignature  = $this->signByPriKey($platformPubKey.'_'.$addr.'_destroy_point_'.$count.'_'.$operateId)['signedData'];
        $params = [
            'addr' => $addr,
            'platformPubKey' => $platformPubKey,
            'count' => $count,
            'operateId' => $operateId,
            'signature' => $signature,
            'platformSignature' => $platformSignature,
        ];
        return  $this->httpPostJson('/api/v1/nft/point/destroy', $params);
    }

    /**
     * 查询积分销毁结果
     * (c) moshong <9080@live.com>
     * @param string $taskId 任务ID | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7, 任务失败:10 | string taskMsg 失败情况下会有提示信息 | string txHash 交易hash | int chainTimestamp 链上交易时间戳
     */
    public function getDestroyResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务ID不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'taskId' => $taskId,
            'platformPubKey' => $platformPubKey,
        ];
        return  $this->httpGet('/api/v1/nft/point/destroy/result', $params);
    }


    /**
     * 积分查询
     * (c) moshong <9080@live.com>
     * @param string $taskId 任务ID | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息 ，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int count 积分数量
     */
    public function queryPoint($addr='')
    {
        if (!$addr){
            throw new Exception('查询人地址不能为空');
        }
        $platformAddr = $this->app->config['address']?$this->app->config['address']:$this->priKey2Address($this->app->config['priKey'])["address"];
        $params = [
            'addr' => $addr,
            'platformAddr' => $platformAddr,
        ];
        return  $this->httpGet('/api/v1/nft/point/query', $params);
    }

    /**
     * NFT 系列声明
     * (c) moshong <9080@live.com>
     * @param string $priKey 系列声明人私钥 | required
     * @param string $seriesName 系列名，不超过30个字符 | required
     * @param string $coverUrl 系系列封面url，不超过1024个字符 | required
     * @param string $desc 系列描述信息，不超过500个字符 | required
     * @param bool $seriesBeginFromZero 系列下的nftId后缀，是否从0开始，true就是从0开始，默认为false，从1开始。
     * @param int $totalCount 系列一共有多少个。0表示没有限制 | required
     * @param string $operateId 请求ID,重复请求传上次id，新请求无需。 |
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function claimSeries($priKey='',$seriesName='',$coverUrl='',$desc='',$seriesBeginFromZero=true,$totalCount=0,$operateId='')
    {
        if (!$priKey || !$seriesName){
            throw new Exception('声明人私钥或系列名不能为空');
        }
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $pubKey = $pubResult["pub"];
        $operateId = $operateId?$operateId:$this->create_uuid();
        $platformPubKey = $this->app->config['pubKey'];
        $signText = $platformPubKey.'_'.$pubKey.'_series_claim_'.$seriesName.'_'.$totalCount.'_'.$coverUrl.'_'.$desc.'_0_'.$seriesBeginFromZero.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $platformSignature  = $this->signByPriKey($signText)['signedData'];
        $params = [
            'pubKey' => $pubKey,
            'platformPubKey' => $platformPubKey,
            'seriesName' => $seriesName,
            'totalCount' => $totalCount,
            'operateId' => $operateId,
            'coverUrl' => $coverUrl,
            'desc' => $desc,
            'signature' => $signature,
            'platformSignature' => $platformSignature,
            'seriesBeginFromZero'=>$seriesBeginFromZero,
            'maxPublishCount'=>0
        ];
        return  $this->httpPostJson('/api/v1/nft/series/claim', $params);
    }

    /**
     * 查询 NFT 系列声明结果
     * (c) moshong <9080@live.com>
     * @param string $taskId 任务ID | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7, 任务失败:10 | string taskMsg 失败情况下会有提示信息 | string txHash 交易hash | int chainTimestamp 链上交易时间戳
     */
    public function claimSeriesResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务ID不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'taskId' => $taskId,
            'platformPubKey' => $platformPubKey,
        ];
        return  $this->httpGet('/api/v1/nft/series/claim/result', $params);
    }



    /**
     * 查询系列信息
     * (c) moshong <9080@live.com>
     * @param string $seriesId 系列Id | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - seriesId 系列id|name 系列名字|creatorAddr 创建者地址|totalCount 总数|seriesBeginFromZero 系列下的nftId后缀，是否从0开始，true就是从0开始，默认为false，从1开始。|crtCount 当前个数（当前已发行此系列的个数）|coverUrl 封面图|desc 描述|createTimeStamp 创建时间戳
     */
    public function getSeries($seriesId='')
    {
        if (!$seriesId){
            throw new Exception('系列Id不能为空');
        }
        $params = [
            'seriesId' => $seriesId,
        ];
        return  $this->httpGet2('/api/v1/nft/series', $params);
    }

    /**
     * 查询该账户资产归属的系列列表
     * (c) moshong <9080@live.com>
     * @param string $addr 账户地址 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data seriesList- seriesId 系列id|name 系列名字|creatorAddr 创建者地址|totalCount 总数|seriesBeginFromZero 系列下的nftId后缀，是否从0开始，true就是从0开始，默认为false，从1开始。|crtCount 当前个数（当前已发行此系列的个数）|coverUrl 封面图|desc 描述|createTimeStamp 创建时间戳
     */
    public function seriesList($addr='')
    {
        if (!$addr){
            throw new Exception('账户地址不能为空');
        }
        $params = [
            'addr' => $addr,
        ];
        return  $this->httpGet2('/api/v1/nft/series/list', $params);
    }

    /**
     * 发行 NFT
     * 注意：相同Hash在一个系列中，目前最大可发3000份。
     * (c) moshong <9080@live.com>
     * @param string $priKey 系列声明人私钥 | required
     * @param string $author 作者名，中文+英文（数字或符号为非法输入）不超过30个字符。 | required
     * @param string $name nft名字，中英文数字均可，不超过256个字符。 | required
     * @param string $url 介质url，不超过1024个字符 | required
     * @param string $displayUrl 预览图url，不超过1024个字符。（至信链浏览器展示预览图尺寸为290*290，请上传比例为1:1的图片） | required
     * @param string $desc nft简介，500个字符以内 | required
     * @param string $flag 标签，【文创】【游戏】【动漫】······，30个字符以内 | （签名中必填，没有为“”）
     * @param string $seriesId 系列id | （签名中必填，没有为“”）
     * @param int $publishCount 发行量，如果没有系列，就只能为1，如果有系列从1开始，比如如有100个，系列id范围则为[1-100]，单次发行个数不超过1000，同系列下同介质个数总共不能超过5000| required
     * @param int $seriesBeginIndex 系列子ID从多少开始，没有系列只能填1。有系列情况下，根据系列声明时指定seriesBeginFromZero决定是否可以从0开始。总体上不超过系列的最大值，（比如系列如果从1开始，最大值为100，系列ID只能从1-100）| required
     * @param int $sellStatus 1:可售 2:不可售 | required
     * @param int64 $sellCount 售状态下有意义，表示售卖多少积分 |（签名中必填，没有为0）
     * @param string $operateId 请求ID，每个请求需要填唯一的id，重复请求用相同的id。为了保证唯一性，必须使用uuid| required
     * @param string $metaData 扩展字段，用户自定义，长度不超过1024个字符
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function publishNft($priKey='',$author='',$name='',$url='',$displayUrl='',$desc='',$flag='',$seriesId='',$publishCount=1,$seriesBeginIndex=1,$sellStatus=1,$sellCount=0,$operateId='',$metaData='',$hash='')
    {
        if (!$priKey || !$author || !$name || !$url || !$displayUrl || !$desc){
            throw new Exception('声明人私钥或系列名不能为空');
        }
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $pubKey = $pubResult["pub"];
        $operateId = $operateId?$operateId:$this->create_uuid();
        $platformPubKey = $this->app->config['pubKey'];
        $hash = $hash?$hash:$this->sm3Hash(Bytes::getUrlBytes($url));
        $signText = $platformPubKey.'_'.$pubKey.'_publish_nft_'.$author.'_'.$name.'_'.$url.'_'.$displayUrl.'_'.$hash.'_'.$desc.'_'.$flag.'_'.$publishCount.'_'.$seriesId.'_'.$seriesBeginIndex.'_'.$sellStatus.'_'.$sellCount.'_'.$metaData.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $platformSignature  = $this->signByPriKey($signText)['signedData'];
        $params = [
            'pubKey' => $pubKey,
            'platformPubKey' => $platformPubKey,
            'author' => $author,
            'name' => $name,
            'url' => $url,
            'displayUrl' => $displayUrl,
            'hash' => $hash,
            'desc' => $desc,
            'flag' => $flag,
            'publishCount' => $publishCount,
            'seriesId' => $seriesId,
            'seriesBeginIndex' => $seriesBeginIndex,
            'sellStatus' => $sellStatus,
            'sellCount' => $sellCount,
            'operateId' => $operateId,
            'metaData' => $metaData,
            'signature' => $signature,
            'platformSignature' => $platformSignature,
        ];
        return  $this->httpPostJson('/api/v1/nft/publish', $params);
    }


    /**
     * 查询 NFT 发行结果
     * (c) moshong <9080@live.com>
     * @param string $taskId 任务ID | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7, 任务失败:10 | string taskMsg 失败情况下会有提示信息 | string txHash 交易hash | int chainTimestamp 链上交易时间戳 | nftIdBegin nftId格式，发行人公钥hash_系列_系列索引id，申请多少个，最后一段计算出来即可，比如申请10个,nftIdBegin位xx_xx_1，那么就可以推导出x_xx_1到x_xx_10 | txHash 交易hash
     */
    public function getPublishResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务ID不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'taskId' => $taskId,
            'platformPubKey' => $platformPubKey,
        ];
        return  $this->httpGet('/api/v1/nft/publish/result', $params);
    }

    /**
     * NFT元信息查询
     * (c) moshong <9080@live.com>
     * @param string $nftId 查询nft信息 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - json nftInfo nft元信息
     *
     */
    public function getInfo($nftId='')
    {
        if (!$nftId){
            throw new Exception('NFT ID不能为空');
        }
        $params = [
            'nftId' => $nftId,
        ];
        return  $this->httpGet2('/api/v1/nft/info', $params);
    }

    /**
     * 查询账户NFT列表
     * (c) moshong <9080@live.com>
     * @param string $addr 账户地址 | required
     * @param string $seriesId 通过系列id过滤 |
     * @param int $orderBy 1：按ownerGainedTime倒序排序 2：按seriesIndexId倒序排序 不填默认为0：按createTime倒序排序 |
     * @param int64 $offset 查询偏移量，从0开始，和mysql一个语义 |
     * @param int64 $limit 最大1000 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int64 total 藏品总数 | json nftList nft元信息列表
     *
     */
    public function getListByAddress($addr='',$seriesId='',$orderBy=0,$offset=0,$limit=10)
    {
        if (!$addr){
            throw new Exception('账户地址不能为空');
        }
        $params = [
            'addr' => $addr,
            'orderBy' => $orderBy,
            'offset'=> $offset,
            'limit'=>$limit
        ];
        if ($seriesId) $params['seriesId']=$seriesId;
        return  $this->httpGet2('/api/v1/nft/address/list', $params);
    }
    /**
     * 查询无系列NFT列表
     * (c) moshong <9080@live.com>
     * @param string $addr 账户地址 | required
     * @param int64 $offset 查询偏移量，从0开始，和mysql一个语义 |
     * @param int64 $limit 最大1000 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int64 total 藏品总数 | json nftList nft元信息列表
     *
     */
    public function getListWithoutSeries($addr='',$offset=0,$limit=10)
    {
        if (!$addr){
            throw new Exception('账户地址不能为空');
        }
        $params = [
            'addr' => $addr,
            'offset'=> $offset,
            'limit'=>$limit
        ];
        return  $this->httpGet2('/api/v1/nft/address/without/series/list', $params);
    }

    /**
     * 查询NFT交易信息列表
     * (c) moshong <9080@live.com>
     * @param string $nftId nftId | required
     * @param int64 $offset 查询偏移量，从0开始，和mysql一个语义 |
     * @param int64 $limit 最大1000 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int64 total 交易信息总数 | json transList nft元信息列表
     *
     */
    public function getTradeList($nftId='',$offset=0,$limit=10)
    {
        if (!$nftId){
            throw new Exception('nftId不能为空');
        }
        $params = [
            'nftId' => $nftId,
            'offset'=> $offset,
            'limit'=>$limit
        ];
        return  $this->httpGet2('/api/v1/nft/trade/list', $params);
    }

    /**
     * 查询转入NFT交易信息列表
     * (c) moshong <9080@live.com>
     * @param string $addr 账户地址 | required
     * @param int64 $offset 查询偏移量，从0开始，和mysql一个语义 |
     * @param int64 $limit 最大1000 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int64 total 交易信息总数 | json transList nft元信息列表
     *
     */
    public function getTradeInList($addr='',$offset=0,$limit=10)
    {
        if (!$addr){
            throw new Exception('账户地址不能为空');
        }
        $params = [
            'addr' => $addr,
            'offset'=> $offset,
            'limit'=>$limit
        ];
        return  $this->httpGet2('/api/v1/nft/trade/in/list', $params);
    }
    /**
     * 查询转出NFT交易信息列表
     * (c) moshong <9080@live.com>
     * @param string $addr 账户地址 | required
     * @param int64 $offset 查询偏移量，从0开始，和mysql一个语义 |
     * @param int64 $limit 最大1000 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int64 total 交易信息总数 | json transList nft元信息列表
     *
     */
    public function getTradeOutList($addr='',$offset=0,$limit=10)
    {
        if (!$addr){
            throw new Exception('账户地址不能为空');
        }
        $params = [
            'addr' => $addr,
            'offset'=> $offset,
            'limit'=>$limit
        ];
        return  $this->httpGet2('/api/v1/nft/trade/out/list', $params);
    }

    /**
     * 查询地址下NFT交易列表
     * (c) moshong <9080@live.com>
     * @param string $addr 账户地址 | required
     * @param int64 $offset 查询偏移量，从0开始，和mysql一个语义 |
     * @param int64 $limit 最大1000 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int64 total 交易信息总数 | json transList nft元信息列表
     *
     */
    public function getTradeAllList($addr='',$offset=0,$limit=10)
    {
        if (!$addr){
            throw new Exception('账户地址不能为空');
        }
        $params = [
            'addr' => $addr,
            'offset'=> $offset,
            'limit'=>$limit
        ];
        return  $this->httpGet2('/api/v1/nft/trade/all/list', $params);
    }

    /**
     * 查询NFT列表（可区分是否同名操作）
     * (c) moshong <9080@live.com>
     * @param string $nftId nftId | required
     * @param int $txType 交易类别，1:发行; 2:购买 3:转移; 4.设置价格;5.设置状态。 不填为所有类别
     * @param int $sameUser  0表示不区分；1：只要同名 2：只要非同名。该参数只在txType为3的时候有意义
     * @param int64 $offset 查询偏移量，从0开始，和mysql一个语义 |
     * @param int64 $limit 最大1000 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int64 total 交易信息总数 | json transList nft元信息列表
     *
     */
    public function getTradeListBySame($nftId='',$txType=0,$sameUser=0,$offset=0,$limit=10)
    {
        if (!$nftId){
            throw new Exception('nftId不能为空');
        }
        $params = [
            'nftId' => $nftId,
            'sameUser' => $sameUser,
            'offset'=> $offset,
            'limit'=>$limit
        ];
        if($txType>0) $params['txType']=$txType;
        return  $this->httpGet('/api/v2/nft/trade/list', $params);
    }


    /**
     * NFT 购买（可以申请积分，此接口只购买，不到账，到账由taskman内部发起任务）
     * (c) moshong <9080@live.com>
     * @param string $nftId 要购买的nftId | required
     * @param int $applyScore 申请多少积分给购买者 | required
     * @param string $priKey nft接收者私钥（也就是购买者） | required
     * @param int $offerCount 出多少积分 | required
     * @param string $operateId 请求ID，每个请求需要填唯一的id，重复请求用相同的id。为了保证唯一性，必须使用uuid| required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function buyNft($nftId='',$applyScore=0,$priKey='',$offerCount=0,$operateId='')
    {
        if (!$priKey || !$nftId){
            throw new Exception('nftId或t接收者私钥不能为空');
        }
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $receiverPubKey = $pubResult["pub"]; //nft接收者公钥（也就是购买者）
        $adrResult = $this->priKey2Address($this->app->config['priKey']);
        if ($adrResult["err"])
        {
            throw new Exception($adrResult["err"]);
        }
        $pointReceiverAddr = $adrResult["address"]; //积分接收地址
        $operateId = $operateId?$operateId:$this->create_uuid();
        $platformPubKey = $this->app->config['pubKey'];
        $signText = $platformPubKey.'_'.$receiverPubKey.'_'.$pointReceiverAddr.'_'.$applyScore.'_buy_nft_'.$nftId.'_'.$offerCount.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $platformSignature  = $this->signByPriKey($signText)['signedData'];
        $params = [
            'nftId' => $nftId,
            'applyScore' => $applyScore,
            'receiverPubKey' => $receiverPubKey,
            'pointReceiverAddr' => $pointReceiverAddr,
            'platformPubKey' => $platformPubKey,
            'offerCount' => $offerCount,
            'operateId' => $operateId,
            'signature' => $signature,
            'platformSignature' => $platformSignature,
        ];
        return  $this->httpPostJson('/api/v1/nft/buy', $params);
    }

    /**
     * 查询 NFT 购买结果
     * (c) moshong <9080@live.com>
     * @param string $taskId 任务ID | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7, 任务失败:10 | string taskMsg 失败情况下会有提示信息 | string txHash 交易hash | int chainTimestamp 链上交易时间戳
     */
    public function buyResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务ID不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'taskId' => $taskId,
            'platformPubKey' => $platformPubKey,
        ];
        return  $this->httpGet('/api/v1/nft/buy/result', $params);
    }

    /**
     * 查询nft购买支付结果
     * (c) moshong <9080@live.com>
     * @param string $taskId 任务ID | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7, 任务失败:10 | string taskMsg 失败情况下会有提示信息 | string txHash 交易hash | int chainTimestamp 链上交易时间戳
     */
    public function buyPayResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务ID不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'taskId' => $taskId,
            'platformPubKey' => $platformPubKey,
        ];
        return  $this->httpGet('/api/v1/nft/buy/pay/result', $params);
    }

    /**
     * NFT 转移
     * (c) moshong <9080@live.com>
     * @param string $nftId 要转移的nftId | required
     * @param string $priKey nft可操作者的私钥 | required
     * @param string $receiverAddr nft接收者的地址 | required
     * @param string $operateId 请求ID，每个请求需要填唯一的id，重复请求用相同的id。为了保证唯一性，必须使用uuid| required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function transferNft($nftId='',$priKey='',$receiverAddr='',$operateId='')
    {
        if (!$priKey || !$nftId || !$receiverAddr){
            throw new Exception('nftId或接收者私钥等参数不能为空');
        }
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $pubKey = $pubResult["pub"]; //nft可操作者的公钥
        $operateId = $operateId?$operateId:$this->create_uuid();
        $signText = $pubKey.'_'.$receiverAddr.'_nft_transfer_'.$nftId.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $params = [
            'pubKey' => $pubKey,
            'receiverAddr' => $receiverAddr,
            'nftId' => $nftId,
            'operateId' => $operateId,
            'signature' => $signature,
        ];
        return  $this->httpPostJson2('/api/v1/nft/transfer', $params);
    }

    /**
     * NFT 同名转移
     * (c) moshong <9080@live.com>
     * @param string $nftId 要转移的nftId | required
     * @param string $priKey nft可操作者的私钥 | required
     * @param string $receiverAddr nft接收者的地址 | required
     * @param string $operateId 请求ID，每个请求需要填唯一的id，重复请求用相同的id。为了保证唯一性，必须使用uuid| required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function transferNftSelf($nftId='',$priKey='',$receiverAddr='',$operateId='')
    {
        if (!$priKey || !$nftId || !$receiverAddr){
            throw new Exception('nftId或接收者私钥等参数不能为空');
        }
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $pubKey = $pubResult["pub"]; //nft可操作者的公钥
        $operateId = $operateId?$operateId:$this->create_uuid();
        $signText = $pubKey.'_'.$receiverAddr.'_nft_transfer_'.$nftId.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $params = [
            'pubKey' => $pubKey,
            'receiverAddr' => $receiverAddr,
            'nftId' => $nftId,
            'operateId' => $operateId,
            'signature' => $signature,
        ];
        return  $this->httpPostJson('/api/v1/nft/self_transfer', $params);
    }


    /**
     * NFT 批量转移
     * (c) moshong <9080@live.com>
     * @param string $nftIds 要转移的nftId列表,逗号分割，需按照字母序排列。最大30个 | required
     * @param string $priKey nft可操作者的私钥 | required
     * @param string $receiverAddr nft接收者的地址 | required
     * @param string $operateId 请求ID，每个请求需要填唯一的id，重复请求用相同的id。为了保证唯一性，必须使用uuid| required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function transferNftBatch($nftIds='',$priKey='',$receiverAddr='',$operateId='')
    {
        if (!$priKey || !$nftIds || !$receiverAddr){
            throw new Exception('nftId或接收者私钥等参数不能为空');
        }
        $nftIds = explode(',',$nftIds);
        $nftIds = sort($nftIds,SORT_STRING);
        $nftIdsText = implode('_',$nftIds);
        $nftIds = json_encode($nftIds);
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $pubKey = $pubResult["pub"]; //nft可操作者的公钥
        $operateId = $operateId?$operateId:$this->create_uuid();
        $signText = $pubKey.'_'.$receiverAddr.'_transferBatch_'.$nftIdsText.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $params = [
            'pubKey' => $pubKey,
            'receiverAddr' => $receiverAddr,
            'nftIds' => $nftIds,
            'operateId' => $operateId,
            'signature' => $signature,
        ];
        return  $this->httpPostJson2('/api/v1/nft/batch_transfer', $params);
    }

    /**
     * NFT转移状态查询
     * (c) moshong <9080@live.com>
     * @param string $operatorPubKey nft可操作者的公钥 | required
     * @param int64 $taskId 查询任务结果 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7,任务失败:1 | string taskMsg 失败情况下会有提示信息 ｜ string txHash 交易hash｜int64 chainTimestamp 链上交易时间戳
     *
     */
    public function transferResult($operatorPubKey='',$taskId='')
    {
        if (!$operatorPubKey || !$taskId){
            throw new Exception('可操作者公钥或任务id不能为空');
        }
        $params = [
            'operatorPubKey' => $operatorPubKey,
            'taskId'=> $taskId,
        ];
        return  $this->httpGet2('/api/v1/nft/transfer/result', $params);
    }


    /**
     * NFT销售状态变更
     * (c) moshong <9080@live.com>
     * @param string $nftId 要操作的nftId | required
     * @param string $priKey nft可操作者的私钥 | required
     * @param string $transStatus 销售状态，1:可售状态:2；非可售状态 默认=1
     * @param string $transPrice 销售价格（默认为0，状态为可售时不能为0）
     * @param string $operateId 请求ID，每个请求需要填唯一的id，重复请求用相同的id。为了保证唯一性，必须使用uuid| required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function updateNftStatus($nftId='',$priKey='',$transStatus=1,$transPrice=0,$operateId='')
    {
        if (!$priKey || !$nftId){
            throw new Exception('nftId或接收者私钥等参数不能为空');
        }
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $operatorPubKey = $pubResult["pub"]; //nft可操作者的公钥
        $platformPubKey = $this->app->config['pubKey'];
        $operateId = $operateId?$operateId:$this->create_uuid();
        $signText = $platformPubKey.'_'.$operatorPubKey.'_nft_update_sell_'.$nftId.'_'.$transStatus.'_'.$transPrice.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $platformSignature = $this->signByPriKey($signText,$this->app->config['priKey'])['signedData'];
        $params = [
            'platformPubKey' => $platformPubKey,
            'operatorPubKey' => $operatorPubKey,
            'nftId' => $nftId,
            'transStatus' => $transStatus,
            'transPrice' => $transPrice,
            'operateId' => $operateId,
            'signature' => $signature,
            'platformSignature' => $platformSignature
        ];
        return  $this->httpPostJson('/api/v1/nft/status/update', $params);
    }

    /**
     * NFT销售状态变更查询
     * (c) moshong <9080@live.com>
     * @param int64 $taskId 查询任务结果 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7,任务失败:1 | string taskMsg 失败情况下会有提示信息 ｜ string txHash 交易hash｜int64 chainTimestamp 链上交易时间戳
     *
     */
    public function updateStatusResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务id不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'platformPubKey' => $platformPubKey,
            'taskId'=> $taskId,
        ];
        return  $this->httpGet('/api/v1/nft/status/update/result', $params);
    }


    /**
     * NFT售价变更
     * (c) moshong <9080@live.com>
     * @param string $nftId 要调价的nftId | required
     * @param string $priKey 发起者私钥 | required
     * @param int $transPrice 销售价格，默认设置为1
     * @param string $operateId 请求ID，每个请求需要填唯一的id，重复请求用相同的id。为了保证唯一性，必须使用uuid
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function updateNftPrice($nftId='',$priKey='',$transPrice=1,$operateId='')
    {
        if (!$priKey || !$nftId){
            throw new Exception('nftId或接收者私钥等参数不能为空');
        }
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $operatorPubKey = $pubResult["pub"]; //nft可操作者的公钥
        $platformPubKey = $this->app->config['pubKey'];
        $operateId = $operateId?$operateId:$this->create_uuid();
        $signText = $platformPubKey.'_'.$operatorPubKey.'_nft_update_sell_'.$nftId.'_'.$transPrice.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $platformSignature = $this->signByPriKey($signText,$this->app->config['priKey'])['signedData'];
        $params = [
            'platformPubKey' => $platformPubKey,
            'operatorPubKey' => $operatorPubKey,
            'nftId' => $nftId,
            'transPrice' => $transPrice,
            'operateId' => $operateId,
            'signature' => $signature,
            'platformSignature' => $platformSignature
        ];
        return  $this->httpPostJson('/api/v1/nft/price/update', $params);
    }


    /**
     * NFT售价变更状态查询
     * (c) moshong <9080@live.com>
     * @param int64 $taskId 查询任务结果 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7,任务失败:1 | string taskMsg 失败情况下会有提示信息 ｜ string txHash 交易hash｜int64 chainTimestamp 链上交易时间戳
     *
     */
    public function updatePriceResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务id不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'platformPubKey' => $platformPubKey,
            'taskId'=> $taskId,
        ];
        return  $this->httpGet('/api/v1/nft/price/update/result', $params);
    }

    /**
     * 平台积分转移
     * (c) moshong <9080@live.com>
     * @param string $priKey 转出者私钥（可以与转出者私钥相同，前提是账户有积分可转） | required
     * @param string $toAddr 积分接收者的地址 | required
     * @param int $count 积分数量，默认设置为1
     * @param string $operateId 请求ID，每个请求需要填唯一的id，重复请求用相同的id。为了保证唯一性，必须使用uuid
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - taskId 任务ID
     */
    public function transferNftPoint($priKey='',$toAddr='',$count=1,$operateId='')
    {
        if (!$priKey || !$toAddr){
            throw new Exception('转出者私钥或接收者的地址参数不能为空');
        }
        $pubResult = $this->priKey2PubKey($priKey);
        if ($pubResult["err"])
        {
            throw new Exception($pubResult["err"]);
        }
        $fromPubKey = $pubResult["pub"]; //nft可操作者的公钥
        $platformPubKey = $this->app->config['pubKey'];
        $operateId = $operateId?$operateId:$this->create_uuid();
        $signText = $platformPubKey.'_'.$fromPubKey.'_'.$toAddr.'_transfer_point_'.$count.'_'.$operateId;
        $signature = $this->signByPriKey($signText,$priKey)['signedData'];
        $platformSignature = $this->signByPriKey($signText,$this->app->config['priKey'])['signedData'];
        $params = [
            'platformPubKey' => $platformPubKey,
            'fromPubKey' => $fromPubKey,
            'toAddr' => $toAddr,
            'count' => $count,
            'operateId' => $operateId,
            'signature' => $signature,
            'platformSignature' => $platformSignature
        ];
        return  $this->httpPostJson('/api/v1/nft/point/transfer', $params);
    }

    /**
     * 平台积分转移状态查询
     * (c) moshong <9080@live.com>
     * @param int64 $taskId 查询任务结果 | required
     * @return json
     * @return      int retCode 返回状态码，取值：0-成功， 其他-失败
     * @return      string retMsg 返回信息，取值：成功为OK, 失败-错误原因说明
     * @return      json data - int taskStatus 标记任务状态，任务执行中:2，任务成功:7,任务失败:1 | string taskMsg 失败情况下会有提示信息 ｜ string txHash 交易hash｜int64 chainTimestamp 链上交易时间戳
     *
     */
    public function transferPointResult($taskId='')
    {
        if (!$taskId){
            throw new Exception('任务id不能为空');
        }
        $platformPubKey = $this->app->config['pubKey'];
        $params = [
            'platformPubKey' => $platformPubKey,
            'taskId'=> $taskId,
        ];
        return  $this->httpGet('/api/v1/nft/point/transfer/result', $params);
    }

}