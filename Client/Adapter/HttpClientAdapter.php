<?php

namespace Cravler\FayeAppBundle\Client\Adapter;

use Symfony\Contracts\HttpClient\HttpClientInterface;

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
        $responses = [];
        foreach ($packages as $package) {
            $responses[] = $this->client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $package,
            ]);
        }

        foreach ($this->client->stream($responses) as $chunk) {
            // consume stream to complete all requests concurrently
        }
    }
}
