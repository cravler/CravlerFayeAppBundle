<?php

namespace Cravler\FayeAppBundle\Client;

use Nc\FayeClient\Client as FayeClient;
use Nc\FayeClient\Adapter\AdapterInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Client implements ClientInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @param AdapterInterface $adapter
     * @param array $config
     */
    public function __construct(AdapterInterface $adapter, array $config)
    {
        $this->client = new FayeClient(
            $adapter,
            $config['scheme'] . '://' . $config['host'] . ':' . $config['port'] . $config['mount']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function send($channel, $data = array(), $ext = array())
    {
        $this->client->send($channel, $data, $ext);
    }
}
