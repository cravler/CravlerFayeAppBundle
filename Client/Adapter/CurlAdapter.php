<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class CurlAdapter implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public function postJSON($url, $body)
    {
        $cmd = "curl -X POST -H 'Content-Type: application/json'";
        $cmd.= " -d '" . $body . "' " . "'" . $url . "'";
        $cmd .= " > /dev/null 2>&1 &";
        exec($cmd, $output, $exit);
    }
}
