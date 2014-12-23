<?php

namespace Cravler\FayeAppBundle\Service;

use Cravler\FayeAppBundle\EntryPoint\EntryPointInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class EntryPointsChain
{
    /**
     * @var array
     */
    private $entryPoints = array();

    /**
     * @param $entryPoint
     */
    public function addEntryPoint($entryPoint)
    {
        if ($entryPoint instanceof EntryPointInterface) {
            $this->entryPoints[$entryPoint->getId()] = $entryPoint;
        }
    }

    /**
     * @return null|EntryPointInterface
     */
    public function getEntryPoint($id)
    {
        if (isset($this->entryPoints[$id])) {
            return $this->entryPoints[$id];
        }
        return null;
    }

    /**
     * @return array
     */
    public function getEntryPoints()
    {
        return $this->entryPoints;
    }
}
