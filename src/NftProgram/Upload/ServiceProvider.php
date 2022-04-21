<?php
/*
 * This file is part of the nft_zxinchain
 * (c) moshong <9080@live.com>
 * Date:2022/4/18 3:28 下午
 * 
 */

namespace NftZxinchainn\NftProgram\Upload;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['upload'] = function ($app) {
            return new Client($app);
        };
    }
}