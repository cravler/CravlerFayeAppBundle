<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
interface AdapterInterface
{
    public function postJSON(string $url, string $package): void;
}
