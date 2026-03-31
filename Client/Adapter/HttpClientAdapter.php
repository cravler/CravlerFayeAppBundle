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

    public function postJSON(string $url, string|array $data): void
    {
        if (!\is_array($data)) {
            $data = [$data];
        }

        $responses = [];
        foreach ($data as $body) {
            $responses[] = $this->client->request('POST', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body' => $body,
            ]);
        }

        foreach ($this->client->stream($responses) as $chunk) {
            // consume stream to complete all requests concurrently
        }
    }
}
