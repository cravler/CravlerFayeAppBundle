<?php

namespace Cravler\FayeAppBundle\Client;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class Client implements ClientInterface
{
    protected string $fayeServerUrl;

    /**
     * @param array{
     *     'url'?: string,
     *     'scheme'?: string,
     *     'host': string,
     *     'port'?: int,
     *     'mount': string,
     * } $config
     */
    public function __construct(
        private readonly Adapter\AdapterInterface $adapter,
        private readonly array $config,
    ) {
        if (\is_string($config['url'] ?? null)) {
            $this->fayeServerUrl = $config['url'];
        } else {
            $scheme = $config['scheme'] ?? 'http';
            $url = $scheme.'://'.$config['host'];

            $port = $config['port'] ?? null;
            if ($port) {
                if (80 === $port && 'http' === $scheme) {
                    $port = null;
                } elseif (443 === $port && 'https' === $scheme) {
                    $port = null;
                }

                if ($port) {
                    $url = $url.':'.$port;
                }
            }

            $this->fayeServerUrl = $url.$config['mount'];
        }
    }

    /**
     * @return array<string, mixed> $config
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function send(array $packages): void
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
