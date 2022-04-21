<?php

namespace NftZxinchainn\Tools\Contracts;

/**
 * Interface EventHandlerInterface.
 *
 * @author mingyoung <mingyoungcheung@gmail.com>
 */
interface EventHandlerInterface
{
    /**
     * @param mixed $payload
     */
    public function handle($payload = null);
}
