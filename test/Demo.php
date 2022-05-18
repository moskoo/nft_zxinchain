<?php
require_once '../vendor/autoload.php';

use NftZxinchainn\Factory;
use NftZxinchainn\Tools\Support\Bytes;

$config = [
        'appId' => '220412000100001',
        'appKey' => 'c28092ba207b4d9da17fcc29c953a7f6',
        'priKey' => "-----BEGIN PRIVATE KEY-----\nMIGTAgEAMBMGByqGSM49AgEGCCqBHM9VAYItBHkwdwIBAQQg8vQDU+aaBjQZOj0V rXgKhSux9dfwpkkjaeEZXqtzppegCgYIKoEcz1UBgi2hRANCAATJKI5jsNEjGk+a fva2t9g4T6j9o7sHmQl1Z0wDZXv9XvolfdD2OhL7i6Y5w0r52or2JaILIx78p7hO KSlVu1iq\n-----END PRIVATE KEY-----\n",
        'pubKey' => "-----BEGIN PUBLIC KEY-----\nMFkwEwYHKoZIzj0CAQYIKoEcz1UBgi0DQgAEySiOY7DRIxpPmn72trfYOE+o/aO7 B5kJdWdMA2V7/V76JX3Q9joS+4umOcNK+dqK9iWiCyMe/Ke4TikpVbtYqg==\n-----END PUBLIC KEY-----\n",
        'identification' => 'd7ee43c9881ae9594529bb9d8d5f3be462eb4257fb8fcf6432f3cad5937f5346',
        'address' => "ZX89fd7b14d32eab21eb7b9ab6c6019c08113743a8",
        'test' => true
    ];
    $app = Factory::nftProgram($config);


    //企业首次绑定
    //$res = $app->wallet->getVerifyCode('320902198502288511',1,1);//获取手机验证码
    //$app->wallet->getUserInfo('320902198502288511','验证码',1);//获取唯一标识
    //$res = $app->auth->deriveKeyPair();//派生生成子公私钥对
    //$res = $app->wallet->getVerifyCode('320902198502288511',1,2);//获取邮件验证码
    //$res = $app->wallet->bindPlatformSelf('邮箱验证码','上面返回公钥','用户唯一标识');//平台自身绑定
    //$res = $app->wallet->queryBindInfo('11111');//检查是否绑定成功


    //$res = $app->wallet->checkAddressToUser('qwqwqwq','qwqwwqwq');
    //$res = $app->upload->getSecret();
    //='/Users/apple/Desktop/Rattler.png';

    //$res = $app->upload->uploadToCos($filePath);
    //$file = fopen('/Users/apple/Desktop/Rattler.png');
    //$res = $app->upload->uploadFileToCos($_FILES);
    //$res = $app->wallet->uploadBusinessLicense($_FILES,$mobile='13222388880',$verifyCode='1313');
    //$res = $app->wallet->uploadLicensePlatform($_FILES);
//var_dump(Bytes::getBytes(file_get_contents($_FILES['file']['tmp_name'])));

$url = 'https://assets.hcq.me/wp-content/uploads/2022/04/1650850673-dd74bf0c88aa84c.png';
var_dump(Bytes::getUrlBytes($url));
exit();
    $res = $app->nft->publishNft('ZX89fd7b14d32eab21eb7b9ab6c6019c08113743a8','23322323');
    var_dump($res);





