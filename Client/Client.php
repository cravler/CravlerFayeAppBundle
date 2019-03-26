<?php

namespace Cravler\FayeAppBundle\Client;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Client implements ClientInterface
{
    /**
     * @var Adapter\AdapterInterface
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $fayeServerUrl;

    /**
     * @param Adapter\AdapterInterface $adapter
     * @param array $config
     */
    public function __construct(Adapter\AdapterInterface $adapter, array $config)
    {
        $this->adapter = $adapter;

        $url = ($config['scheme'] ?: 'http') . '://' . $config['host'];
        if ($config['port']) {
            $url = $url . ':' . $config['port'];
        }
        $this->fayeServerUrl = $url . $config['mount'];
    }

    /**
     * {@inheritdoc}
     */
    public function send(array $packages)
    {
        if ($this->adapter instanceof Adapter\BatchAdapterInterface) {
            $this->adapter->postJSON($this->fayeServerUrl, $packages);
        } else {
            foreach ($packages as $package) {
                $this->adapter->postJSON($this->fayeServerUrl, $package);
            }
        }
    }
}
