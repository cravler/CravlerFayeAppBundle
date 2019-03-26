<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface BatchAdapterInterface extends AdapterInterface
{
    /**
     * @param string $url
     * @param string|string[] $data
     */
    public function postJSON($url, $data);
}
