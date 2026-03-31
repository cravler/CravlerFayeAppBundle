<?php

namespace Cravler\FayeAppBundle\Service;

use Cravler\FayeAppBundle\Ext\ContributorInterface;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class ExtensionsChain
{
    /**
     * @var array<int, ContributorInterface>
     */
    private $extensions = [];

    public function addExtension(mixed $extension): void
    {
        if ($extension instanceof ContributorInterface) {
            $this->extensions[] = $extension;
        }
    }

    /**
     * @return array<int, ContributorInterface>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }
}
