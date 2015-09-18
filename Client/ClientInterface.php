<?php

namespace Cravler\FayeAppBundle\Client;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface ClientInterface
{
    /**
     * Send message
     * @param string $channel message channel
     * @param array  $data    Data to send
     * @param array  $ext     Extra data
     */
    public function send($channel, $data = array(), $ext = array());
}
