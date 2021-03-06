<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface AdapterInterface
{
    /**
     * @param string $url
     * @param string $data
     */
    public function postJSON($url, $data);
}
