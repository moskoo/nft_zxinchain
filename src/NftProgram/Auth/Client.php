<?php
/*
 * This file is part of the moshong/nft_zxinchainn.
 * Tencent Zhixin Chain NFT Platform Interface SDK.
 *
 * (c) moshong <9080@live.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace NftZxinchainn\NftProgram\Auth;

use NftZxinchainn\Tools\BaseClient;
use NftZxinchainn\Tools\ServiceContainer;

/**
 * Class Auth.
 *
 * @author moshong <9080@live.com>
 */
class Client extends BaseClient
{
    protected $localUri = 'http://127.0.0.1:30505';

    /**
     * 生成助记词
     * (c) moshong <9080@live.com>
     * @return JSON  err 错误信息 | mnemonic 助记词
     */

    public function createMnemonic()
    {
        return  $this->httpPostJson($this->localUri.'/createMnemonic', []);
    }

    /**
     * 派生生成子公私钥对
     * (c) moshong <9080@live.com>
     * @param string $mnemonic 助记词 required
     * @param int $index 公私钥对序号 required
     * @return JSON  err 错误信息 | priKey 私钥 | pubKey 公钥
     */

    public function deriveKeyPair($mnemonic='',$index=1)
    {
        $mnemonic = $mnemonic?$mnemonic:$this->createMnemonic()["mnemonic"];
        $params = [
            'mnemonic' => $mnemonic,
            'index' => $index,
        ];
        return  $this->httpPostJson($this->localUri.'/deriveKeyPair', $params);
    }

    /**
     * sdk-签名
     * (c) moshong <9080@live.com>
     * @param string $priKey 私钥 required
     * @param int $data 需要被签名的原数据 required
     * @return JSON  err 错误信息 signedData 签名后的数据
     */

    public function signByPriKey($data=null,$priKey='')
    {
        if (!$data){
            throw new Exception('Data和PriKey参数不能为空');
        }
        $priKey = $priKey?$priKey:$this->app->config['priKey'];
        $params = [
            'priKey' => $priKey,
            'data' => $data,
        ];
        return  $this->httpPostJson($this->localUri.'/signByPriKey', $params);
    }


    /**
     * sdk-私钥生成对应公钥
     * (c) moshong <9080@live.com>
     * @param string $pri 私钥 required
     * @return JSON  err 错误信息 | pub 公钥
     */

    public function priKey2PubKey($pri='')
    {
        if (!$pri){
            throw new Exception('pri私钥不能为空');
        }
        $params = [
            'pri' => $pri,
        ];
        return  $this->httpPostJson($this->localUri.'/priKey2PubKey', $params);
    }

    /**
     * sdk-公钥生成对应地址
     * (c) moshong <9080@live.com>
     * @param string $pubKey 公钥 required
     * @return JSON  err 错误信息 | address 地址
     */
    public function pubKey2Address($pubKey='')
    {
        if (!$pubKey){
            throw new Exception('PubKey公钥不能为空');
        }
        $params = [
            'pubKey' => $pubKey,
        ];
        return  $this->httpPostJson($this->localUri.'/pubKey2Address', $params);
    }

    /**
     * sdk-私钥生成对应地址
     * (c) moshong <9080@live.com>
     * @param string $priKey 私钥 required
     * @return JSON  err 错误信息 | address 地址
     */
    public function priKey2Address($priKey='')
    {
        if (!$priKey){
            throw new Exception('PriKey私钥不能为空');
        }
        $params = [
            'priKey' => $priKey,
        ];
        return  $this->httpPostJson($this->localUri.'/priKey2Address', $params);
    }

    /**
     * sdk-验签
     * (c) moshong <9080@live.com>
     * @param string $pubKey 公钥 required
     * @param string $signedData 签名 required
     * @param string $data 签名前的数据 required
     * @return JSON  err 错误信息 | isValid boolean
     */
    public function verifyByPubKey($pubKey='',$signedData='',$data='')
    {
        if (!$pubKey || !$signedData || !$data){
            throw new Exception('公钥、签名、签名前数据都不可为空');
        }
        $params = [
            'pubKey' => $pubKey,
            'signedData' => $signedData,
            'data' => $data,
        ];
        return  $this->httpPostJson($this->localUri.'/verifyByPubKey', $params);
    }


    /**
     * sdk-SM3哈希
     * (c) moshong <9080@live.com>
     * @param object $data 文件byte数组 required
     * @return JSON  err 错误信息 | digest 介质hash,直接对应发行NFT接口中的hash字段
     */
    public function sm3Hash($data=null)
    {
        if (!$data){
            throw new Exception('文件byte数组都不可为空');
        }
        $params = [
            'data' => $data,
        ];
        return  $this->httpPostJson($this->localUri.'/sm3Hash', $params);
    }
}
