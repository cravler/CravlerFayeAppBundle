<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
interface BatchAdapterInterface extends AdapterInterface
{
    /**
     * @param string[] $packages
     */
    public function postBatch(string $url, array $packages): void;
}
