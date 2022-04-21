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

namespace NftZxinchainn\NftProgram\Wallet;

use NftZxinchainn\NftProgram\Auth\Client as BaseClient;

use NftZxinchainn\Tools\Exception;
use NftZxinchainn\Tools\ServiceContainer;

class Client extends BaseClient
{
    /**
     * 下发注册实名验证码接口
     * (c) moshong <9080@live.com>
     * @param string $mobile 手机号 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - 无
     */
    public function getRegisterCode($mobile)
    {
        if (!$mobile){
            throw new Exception('手机号不能为空');
        }
        $params = [
            'mobile' => $mobile,
        ];
        return  $this->httpPostJson2('/api/v1/nft/register/verify_code', $params);
    }

    /**
     * 自然人注册实名接口
     * (c) moshong <9080@live.com>
     * @param string $personName 用户名 | required
     * @param string $mobile 手机号 | required
     * @param string $verifyCode 手机验证码 | required
     * @param string $idCard 证件号码 | required
     * @param int $cardType 证件类型 1-身份证 2-护照 3-港澳通行证 4-台湾通行证 5-外国人永居身份证 6-港澳台居民居住证 7-其他 | required
     * @param string $email 用户邮箱
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - string userIdentification 用户唯一标识
     */
    public function registerPerson($personName='',$mobile='',$verifyCode='',$idCard='',$cardType=1,$email='')
    {
        if (!$mobile){
            throw new Exception('手机号不能为空');
        }
        if (!$personName){
            throw new Exception('用户名不能为空');
        }
        if (!$verifyCode){
            throw new Exception('验证码不能为空');
        }
        if (!$idCard){
            throw new Exception('证件号不能为空');
        }
        $params = [
            'personName' => $personName,
            'mobile' => $mobile,
            'verifyCode' => $verifyCode,
            'idCard' => $idCard,
            'cardType' => $cardType,
        ];
        if($email) $params['email'] = $email;
        return  $this->httpPostJson2('/api/v1/nft/register/person', $params);
    }

    /**
     * 自然人注册实名（使用nft平台签名）接口
     * (c) moshong <9080@live.com>
     * @param string $personName 用户名 | required
     * @param string $mobile 手机号 | required
     * @param string $idCard 证件号码 | required
     * @param int $cardType 证件类型 1-身份证 2-护照 3-港澳通行证 4-台湾通行证 5-外国人永居身份证 6-港澳台居民居住证 7-其他 | required
     * @param string $email 用户邮箱
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - string userIdentification 用户唯一标识
     */
    public function registerPersonPlatform($personName='',$mobile='',$idCard='',$cardType=1,$email='')
    {
        if (!$mobile){
            throw new Exception('手机号不能为空');
        }
        if (!$personName){
            throw new Exception('用户名不能为空');
        }
        if (!$idCard){
            throw new Exception('证件号不能为空');
        }
        $params = [
            'personName' => $personName,
            'mobile' => $mobile,
            'idCard' => $idCard,
            'cardType' => $cardType,
            'platformPubKey' => $this->app->config['pubKey'],
            'platformSignData' => $this->signByPriKey($personName.'_'.$mobile.'_'.$idCard)['signedData'],
        ];
        if($email) $params['email'] = $email;
        return  $this->httpPostJson('/api/v1/nft/register/person_platform', $params);
    }

    /**
     * 检查地址是否属于同一个用户主体
     * (c) moshong <9080@live.com>
     * @param string $address1 第一个地址 | required
     * @param string $address2 第二个地址 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - bool result - true: 同一主体 false：非同一主体
     */
    public function checkAddressToUser($address1='',$address2='')
    {
        if (!$address1 || !$address2){
            throw new Exception('两个地址不能为空');
        }
        $params = [
            'address1' => $address1,
            'address2' => $address2,
            'platformPubKey' => $this->app->config['pubKey'],
            'platformSignData' => $this->signByPriKey($address1.$address2)['signedData'],
        ];
        return  $this->httpGet('/api/v1/nft/query/user/address/belong_to_user', $params);
    }

    /**
     * 营业执照上传接口-form-data提交方式
     * (c) moshong <9080@live.com>
     * @param file $file 营业执照图片，不超过5m，jpg或png格式 | required
     * @param string $mobile 手机号码 | required
     * @param string $verifyCode 手机验证码 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int id - 图片id
     */
    public function uploadBusinessLicense($file=null,$mobile='',$verifyCode='')
    {
        if (!$mobile || !$verifyCode){
            throw new Exception('手机号和验证码不能为空');
        }
        if (!$file){
            throw new Exception('没有发现file文件');
        }
        $params = [
            'mobile' => $mobile,
            'verifyCode' => $verifyCode,
        ];
        return  $this->httpUpload2('/api/v1/nft/business_license/upload', $file,$params);
    }

    /**
     * 营业执照上传（使用nft平台签名）-form-data提交方式
     * (c) moshong <9080@live.com>
     * @param file $file 营业执照图片，不超过5m，jpg或png格式 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int id - 图片id
     */
    public function uploadLicensePlatform($file=null)
    {
        if (!$file){
            throw new Exception('没有发现file文件');
        }
        $platformIdentification=$this->app->config['identification'];
        $params = [
            'platformIdentification' => $platformIdentification,
            'platformPubKey' => $this->app->config['pubKey'],
            'platformSignData' => $this->signByPriKey($platformIdentification)['signedData'],
        ];
        return  $this->httpUpload('/api/v1/nft/business_license/upload_platform', $file,$params);
    }

    /**
     * 电子公函上传接口-form-data提交方式
     * (c) moshong <9080@live.com>
     * @param file $file 电子公函图片，不超过5m，jpg或png格式 | required
     * @param string $mobile 手机号码 | required
     * @param string $verifyCode 手机验证码 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int id - 图片id
     */
    public function uploadOfficialLetter($file=null,$mobile='',$verifyCode='')
    {
        if (!$mobile || !$verifyCode){
            throw new Exception('手机号和验证码不能为空');
        }
        if (!$file){
            throw new Exception('没有发现file文件');
        }
        $params = [
            'mobile' => $mobile,
            'verifyCode' => $verifyCode,
        ];
        return  $this->httpUpload2('/api/v1/nft/official_letter/upload', $file,$params);
    }

    /**
     * 电子公函上传（使用nft平台签名）-form-data提交方式
     * (c) moshong <9080@live.com>
     * @param file $file 电子公函图片，不超过5m，jpg或png格式 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - int id - 图片id
     */
    public function uploadLetterPlatform($file=null)
    {
        if (!$file){
            throw new Exception('没有发现file文件');
        }
        $platformIdentification=$this->app->config['identification'];
        $params = [
            'platformIdentification' => $platformIdentification,
            'platformPubKey' => $this->app->config['pubKey'],
            'platformSignData' => $this->signByPriKey($platformIdentification)['signedData'],
        ];
        return  $this->httpUpload('/api/v1/nft/official_letter/upload_platform', $file,$params);
    }

    /**
     * 企业注册实名接口
     * (c) moshong <9080@live.com>
     * @param string $epName 企业名称 | required
     * @param string $creditCode 企业信用代码 | required
     * @param int $busiLicenseId 营业执照文件标识，通过调用上传接口后获得 | required
     * @param string $representativeName 法人代表姓名 | required
     * @param string $contact 管理员姓名 | required
     * @param string $mobile 管理员手机 | required
     * @param string $verifyCode 手机验证码 | required
     * @param string $idCard 管理员证件号码 | required
     * @param int $cardType 证件类型 1-身份证 2-护照 3-港澳通行证 4-台湾通行证 5-外国人永居身份证 6-港澳台居民居住证 7-其他 | required
     * @param int $officialLetterId 电子公函盖章扫描件标识，通过调用上传接口后获得
     * @param string $email 企业邮箱
     * @param string $platformName 接入平台名称
     * @param string $platformUrl 接入平台地址
     * @param int $businessType 平台业务类型 (1:金融类 2:版权类 3:其他类 4:未填写)，默认是未填写
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - string userIdentification 用户唯一标识
     */
    public function registerCompany($epName='',$creditCode='',$mobile='',$verifyCode='',$busiLicenseId=0,$representativeName='',$contact='',$idCard='',$cardType=1,$officialLetterId=0,$email='',$platformName='',$platformUrl='',$businessType=4)
    {
        if (!$mobile){
            throw new Exception('手机号不能为空');
        }
        if (!$epName){
            throw new Exception('企业名称不能为空');
        }
        if (!$creditCode){
            throw new Exception('企业信用代码不能为空');
        }
        if ($busiLicenseId<1){
            throw new Exception('营业执照文件未能获取');
        }
        if (!$representativeName){
            throw new Exception('法人代表姓名不能为空');
        }
        if (!$contact){
            $contact = $representativeName;
        }
        if (!$verifyCode){
            throw new Exception('验证码不能为空');
        }
        if (!$idCard){
            throw new Exception('管理员证件号码不能为空');
        }
        $params = [
            'epName' => $epName,
            'creditCode' => $creditCode,
            'busiLicenseId' => $busiLicenseId,
            'representativeName' => $representativeName,
            'contact' => $contact,
            'mobile' => $mobile,
            'verifyCode' => $verifyCode,
            'idCard' => $idCard,
            'cardType' => $cardType,
            'businessType' =>$businessType,
        ];
        if($email) $params['email'] = $email;
        if($officialLetterId>0) $params['officialLetterId'] = $officialLetterId;
        if($platformName) $params['platformName'] = $platformName;
        if($platformUrl) $params['platformUrl'] =$platformUrl;
        return  $this->httpPostJson2('/api/v1/nft/register/company', $params);
    }


    /**
     * 企业注册实名（使用nft平台签名）
     * (c) moshong <9080@live.com>
     * @param string $epName 企业名称 | required
     * @param string $creditCode 企业信用代码 | required
     * @param int $busiLicenseId 营业执照文件标识，通过调用上传接口后获得 | required
     * @param string $representativeName 法人代表姓名 | required
     * @param string $contact 管理员姓名 | required
     * @param string $mobile 管理员手机 | required
     * @param string $idCard 管理员证件号码 | required
     * @param int $cardType 证件类型 1-身份证 2-护照 3-港澳通行证 4-台湾通行证 5-外国人永居身份证 6-港澳台居民居住证 7-其他 | required
     * @param int $officialLetterId 电子公函盖章扫描件标识，通过调用上传接口后获得
     * @param string $email 企业邮箱
     * @param string $platformName 接入平台名称
     * @param string $platformUrl 接入平台地址
     * @param int $businessType 平台业务类型 (1:金融类 2:版权类 3:其他类 4:未填写)，默认是未填写
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - string userIdentification 用户唯一标识
     */
    public function registerCompanyPlatform($epName='',$creditCode='',$mobile='',$busiLicenseId=0,$representativeName='',$contact='',$idCard='',$cardType=1,$officialLetterId=0,$email='',$platformName='',$platformUrl='',$businessType=4)
    {
        if (!$mobile){
            throw new Exception('手机号不能为空');
        }
        if (!$epName){
            throw new Exception('企业名称不能为空');
        }
        if (!$creditCode){
            throw new Exception('企业信用代码不能为空');
        }
        if ($busiLicenseId<1){
            throw new Exception('营业执照文件未能获取');
        }
        if (!$representativeName){
            throw new Exception('法人代表姓名不能为空');
        }
        if (!$contact){
            $contact = $representativeName;
        }
        if (!$idCard){
            throw new Exception('管理员证件号码不能为空');
        }
        $params = [
            'epName' => $epName,
            'creditCode' => $creditCode,
            'busiLicenseId' => $busiLicenseId,
            'representativeName' => $representativeName,
            'contact' => $contact,
            'mobile' => $mobile,
            'idCard' => $idCard,
            'cardType' => $cardType,
            'businessType' =>$businessType,
            'platformPubKey' => $this->app->config['pubKey'],
            'platformSignData' => $this->signByPriKey($epName.'_'.$creditCode.'_'.$representativeName.'_'.$contact.'_'.$mobile.'_'.$idCard)['signedData'],
        ];
        if($email) $params['email'] = $email;
        if($officialLetterId>0) $params['officialLetterId'] = $officialLetterId;
        if($platformName) $params['platformName'] = $platformName;
        if($platformUrl) $params['platformUrl'] =$platformUrl;
        return  $this->httpPostJson('/api/v1/nft/register/company_platform', $params);
    }



    /**
     * 下发查询用户信息验证码接口
     * (c) moshong <9080@live.com>
     * @param int $type 1企业 2个人| required
     * @param string $cardNo 身份证号 | required
     * @param int $scene 验证码场景：1查询用户信息 2 nft平台绑定地址 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - 无
     */
    public function getVerifyCode($cardNo='',$type=1,$scene=1)
    {
        if (!$cardNo){
            throw new Exception('身份证号不能为空');
        }
        $params = [
            'cardNo' => $cardNo,
            'type' => $type,
            'scene' => $scene
        ];
        return  $this->httpPostJson2('/api/v1/nft/user/query/verify_code', $params);
    }

    /**
     * 查询用户信息接口
     * (c) moshong <9080@live.com>
     * @param int $type 1企业 2个人| required
     * @param string $cardNo 身份证号 | required
     * @param string $verifyCode 手机验证码 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - string userIdentification 用户唯一标识
     */
    public function getUserInfo($cardNo='',$verifyCode='',$type=1)
    {
        if (!$cardNo){
            throw new Exception('身份证号不能为空');
        }
        if (!$verifyCode){
            throw new Exception('验证码不能为空');
        }
        $params = [
            'cardNo' => $cardNo,
            'type' => $type,
            'verifyCode' => $verifyCode
        ];
        return  $this->httpGet2('/api/v1/nft/user/query', $params);
    }

    /**
     * NFT地址绑定接口
     * (c) moshong <9080@live.com>
     * @param string $pubKey 公钥| required
     * @param string $userIdentification 用户唯一标识 | required
     * @param string $faceResultId 人脸结果id | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - address地址
     */
    public function bindSubmit($pubKey='',$userIdentification='',$faceResultId='')
    {
        if (!$pubKey || !$userIdentification || !$faceResultId){
            throw new Exception('参数提交不完整');
        }
        $params = [
            'pubKey' => $pubKey,
            'signData' => $this->signByPriKey($userIdentification)['signedData'],
            'userIdentification' => $userIdentification,
            'faceResultId' => $faceResultId
        ];
        return  $this->httpPostJson2('/api/v1/nft/identity/bind/submit', $params);
    }

    /**
     * 受信平台NFT身份绑定接口
     * (c) moshong <9080@live.com>
     * @param string $userPubKey 用户公钥| required
     * @param string $userIdentification 用户唯一标识 | required
     * @param string $faceResultId 人脸结果id | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - address地址
     */
    public function bindSubmitByTrusted($userPubKey='',$userIdentification='',$faceResultId='')
    {
        if (!$userPubKey || !$userIdentification || !$faceResultId){
            throw new Exception('参数提交不完整');
        }
        $userSignData = $this->signByPriKey($userIdentification)['signedData'];
        $params = [
            'userPubKey' => $userPubKey,
            'userSignData' => $userSignData,
            'userIdentification' => $userIdentification,
            'platformPubKey' => $this->app->config['pubKey'],
            'platformSignData' => $this->signByPriKey($userSignData)['signedData'],
        ];
        return  $this->httpPostJson('/api/v1/nft/identity/bind/submit_by_trusted_platform', $params);
    }

    /**
     * NFT平台自身地址绑定接口
     * (c) moshong <9080@live.com>
     * @param string $pubKey 公钥| required
     * @param string $signData 签名信息:sign(userIdentification) | required
     * @param string $userIdentification 用户唯一标识 | required
     * @param string $verifyCode 邮箱验证码 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - address地址
     */
    public function bindPlatformSelf($verifyCode='',$pubKey='',$userIdentification='')
    {
        $pubKey = $pubKey?$pubKey:$this->app->config['pubKey'];
        $userIdentification = $userIdentification?$userIdentification:$this->app->config['identification'];
        $params = [
            'pubKey' => $pubKey,
            'signData' => $this->signByPriKey($userIdentification)['signedData'],
            'userIdentification' => $userIdentification,
            'verifyCode' => $verifyCode
        ];
        return  $this->httpPostJson('api/v1/nft/identity/bind/platform_self', $params);
    }

    /**
     * 绑定状态批量查询接口
     * (c) moshong <9080@live.com>
     * @param string list $addressList query参数。地址列表 | required
     * @return json
     * @return      int retCode 返回状态码
     * @return      string retMsg 返回信息
     * @return      json data - string userIdentification 用户唯一标识
     */
    public function queryBindInfo($addressList=null)
    {
        if (!$addressList){
            throw new Exception('地址列表不能为空');
        }
        $addressList = is_array($addressList)?$addressList:[$addressList];
        $params = [
            'addressList' => $addressList,
        ];
        return  $this->httpGet2('api/v1/nft/identity/bind/query', $params);
    }



}