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
     * @param string|string[] $data
     */
    public function postJSON(string $url, string|array $data): void;
}
