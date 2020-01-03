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
     * @var array
     */
    protected $config;

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
        $this->config = $config;

        if (array_key_exists('url', $config)) {
            $this->fayeServerUrl = $config['url'];
        } else {
            $scheme = $config['scheme'] ?: 'http';
            $url = $scheme . '://' . $config['host'];

            if ($config['port']) {
                $port = $config['port'];

                if (80 == $port && 'http' == $scheme) {
                    $port = null;
                } else if (443 == $port && 'https' == $scheme) {
                    $port = null;
                }

                if ($port) {
                    $url = $url . ':' . $port;
                }
            }

            $this->fayeServerUrl = $url . $config['mount'];
        }
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
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
