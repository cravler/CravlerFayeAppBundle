<?php

namespace Cravler\FayeAppBundle\Service;

use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class EntryPointsChain
{
    /**
     * @var array<string, EntryPointInterface>
     */
    private array $entryPoints = [];

    public function addEntryPoint(mixed $entryPoint): void
    {
        if ($entryPoint instanceof EntryPointInterface) {
            $this->entryPoints[$entryPoint->getId()] = $entryPoint;
        }
    }

    public function getEntryPoint(string $id): ?EntryPointInterface
    {
        return $this->entryPoints[$id] ?? null;
    }

    /**
     * @return array<string, EntryPointInterface>
     */
    public function getEntryPoints(): array
    {
        return $this->entryPoints;
    }
}
