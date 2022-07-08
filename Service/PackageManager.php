<?php

namespace Cravler\FayeAppBundle\Service;

use Cravler\FayeAppBundle\Client\ClientInterface;
use Cravler\FayeAppBundle\Package\Package;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class PackageManager
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var array
     */
    protected $packages = array();

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param Package $package
     */
    public function persist(Package $package)
    {
        $this->packages[] = $package;
    }

    /**
     * @param Package|null $package
     */
    public function flush(Package $package = null)
    {
        $packages = array();

        if ($package) {
            $key = array_search($package, $this->packages);
            if (false !== $key) {
                unset($this->packages[$key]);
                $packages[] = (string) $package;
            }
        } else {
            foreach ($this->packages as $package) {
                $packages[] = (string) $package;
            }
            $this->packages = array();
        }

        if (count($packages)) {
            $this->client->send($packages);
        }
    }

    public function onTerminate()
    {
        $this->flush();
    }
}
