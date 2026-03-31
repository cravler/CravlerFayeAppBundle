<?php

namespace Cravler\FayeAppBundle\Service;

use Cravler\FayeAppBundle\Client\ClientInterface;
use Cravler\FayeAppBundle\Package\Package;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class PackageManager
{
    /**
     * @var array<int, Package>
     */
    private array $packages = [];

    public function __construct(
        private readonly ClientInterface $client,
    ) {
    }

    public function persist(Package $package): void
    {
        $this->packages[] = $package;
    }

    public function flush(?Package $package = null): void
    {
        $packages = [];

        if ($package) {
            $key = \array_search($package, $this->packages);
            if (false !== $key) {
                unset($this->packages[$key]);
                $packages[] = (string) $package;
            }
        } else {
            foreach ($this->packages as $package) {
                $packages[] = (string) $package;
            }
            $this->packages = [];
        }

        if (\count($packages)) {
            $this->client->send($packages);
        }
    }

    public function onTerminate(): void
    {
        $this->flush();
    }
}
