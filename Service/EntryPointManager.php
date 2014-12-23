<?php

namespace Cravler\FayeAppBundle\Service;

use Nc\FayeClient\Client;
use Nc\FayeClient\Adapter\CurlAdapter;
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
     * @param array $config
     */
    public function __construct(SecurityManager $sm, array $config)
    {
        $this->sm = $sm;

        $this->client = new Client(
            new CurlAdapter(),
            $config['scheme'] . '://' . $config['host'] . ':' . $config['port'] . $config['mount']
        );
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