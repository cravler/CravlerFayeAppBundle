<?php

namespace Cravler\FayeAppBundle\Client;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
interface ClientInterface
{
    /**
     * @param string[] $packages
     */
    public function send(array $packages): void;
}
