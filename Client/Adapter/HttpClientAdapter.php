<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class HttpClientAdapter implements BatchAdapterInterface
{
    public function __construct(
        private readonly HttpClientInterface $client,
    ) {
    }

    public function postJSON(string $url, string $package): void
    {
        $this->postBatch($url, [$package]);
    }

    public function postBatch(string $url, array $packages): void
    {
        $factories = [];
        foreach ($packages as $package) {
            $factories[] = fn() => $this->request($url, $package);
        }

        $this->stream($factories);
    }

    protected function request(string $url, string $package): ResponseInterface
    {
        return $this->client->request('POST', $url, [
            'headers' => ['Content-Type' => 'application/json'],
            'body' => $package,
        ]);
    }

    /**
     * @param callable[] $factories
     */
    protected function stream(array $factories): void
    {
        /** @var ResponseInterface[] $responses */
        $responses = \array_map(static fn($factory) => $factory(), $factories);

        try {
            foreach ($this->client->stream($responses) as $chunk) {
                // consume stream to complete all requests concurrently
            }
        } catch (\Throwable) {}
    }
}
