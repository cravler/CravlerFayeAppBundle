<?php

namespace Cravler\FayeAppBundle\Service;

use Nc\FayeClient\Client;
use Nc\FayeClient\Adapter\CurlAdapter;
use Cravler\FayeAppBundle\Client\ClientInterface;
use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class EntryPointManager
{
    /**
     * @var SecurityManager
     */
    private $sm;

    /**
     * @var Client
     */
    private $client;

    /**
     * @param SecurityManager $sm
     * @param ClientInterface $client
     */
    public function __construct(SecurityManager $sm, ClientInterface $client)
    {
        $this->sm = $sm;
        $this->client = $client;
    }

    /**
     * @return SecurityManager
     */
    public function getSecurityManager()
    {
        return $this->sm;
    }

    /**
     * @param EntryPointInterface $entryPoint
     * @param string $channel
     * @param mixed $data
     */
    public function publish(EntryPointInterface $entryPoint, $channel, $data = null)
    {
        $channel = '/' . str_replace('.', '~', $entryPoint->getId()) . $channel;
        $this->client->send($channel, $data, array(
            'security' => array(
                'system' => $this->getSecurityManager()->createSystemToken()
            )
        ));
    }
}