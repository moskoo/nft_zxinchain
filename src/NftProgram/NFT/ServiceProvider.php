<?php
/*
 * This file is part of the nft_zxinchain
 * (c) moshong <9080@live.com>
 * Date:2022/4/22 9:03 上午
 * 
 */

namespace NftZxinchainn\NftProgram\NFT;


use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}.
     */
    public function register(Container $app)
    {
        $app['nft'] = function ($app) {
            return new Client($app);
        };
    }
}