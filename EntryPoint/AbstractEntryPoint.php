<?php

namespace Cravler\FayeAppBundle\EntryPoint;

use Cravler\FayeAppBundle\Package\Package;
use Cravler\FayeAppBundle\Service\EntryPointManager;
use Symfony\Contracts\Service\Attribute\Required;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
abstract class AbstractEntryPoint implements EntryPointInterface
{
    private EntryPointManager $epm;

    #[Required]
    public function setEntryPointManager(EntryPointManager $epm): void
    {
        $this->epm = $epm;
    }

    public function getEntryPointManager(): EntryPointManager
    {
        return $this->epm;
    }

    public function publish(string $channel, mixed $data = null): Package
    {
        $package = $this->epm->createPackage($this, $channel, $data);

        $this->epm->getPackageManager()->persist($package);

        return $package;
    }

    public function flush(Package $package): void
    {
        $this->epm->getPackageManager()->flush($package);
    }

    public function isGranted(string $type, string $channel, array $message): bool
    {
        return self::TYPE_SUBSCRIBE === $type;
    }

    public function useCache(string $type, string $channel, array $message): bool|int
    {
        return false;
    }
}
