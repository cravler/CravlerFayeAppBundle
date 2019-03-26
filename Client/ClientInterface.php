<?php

namespace Cravler\FayeAppBundle\Client;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface ClientInterface
{
    /**
     * @param array $packages
     */
    public function send(array $packages);
}
