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
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class EventDispatcherServiceProvider.
 *
 * @author mingyoung <mingyoungcheung@gmail.com>
 */
class EventDispatcherServiceProvider implements ServiceProviderInterface
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
        !isset($pimple['events']) && $pimple['events'] = function ($app) {
            $dispatcher = new EventDispatcher();

            foreach ($app->config->get('events.listen', []) as $event => $listeners) {
                foreach ($listeners as $listener) {
                    $dispatcher->addListener($event, $listener);
                }
            }

            return $dispatcher;
        };
    }
}
