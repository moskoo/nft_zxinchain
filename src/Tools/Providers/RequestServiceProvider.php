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

namespace NftZxinchainn\Tools\Providers;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class RequestServiceProvider.
 *
 * @author overtrue <i@overtrue.me>
 */
class RequestServiceProvider implements ServiceProviderInterface
{
    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple A container instance
     */
    public function register(Container $pimple)
    {
        !isset($pimple['request']) && $pimple['request'] = function () {
            return Request::createFromGlobals();
        };
    }
}
