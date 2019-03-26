<?php

namespace Cravler\FayeAppBundle\EntryPoint;

use Cravler\FayeAppBundle\Package\Package;
use Cravler\FayeAppBundle\Service\EntryPointManager;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
abstract class AbstractEntryPoint implements EntryPointInterface
{
    /**
     * @var EntryPointManager
     */
    private $epm;

    /**
     * @param EntryPointManager $epm
     */
    public function setEntryPointManager(EntryPointManager $epm)
    {
        $this->epm = $epm;
    }

    /**
     * @return EntryPointManager
     */
    public function getEntryPointManager()
    {
        return $this->epm;
    }

    /**
     * {@inheritdoc}
     */
    public function publish($channel, $data = null)
    {
        $package = $this->epm->createPackage($this, $channel, $data);

        $this->epm->getPackageManager()->persist($package);

        return $package;
    }

    /**
     * {@inheritdoc}
     */
    public function flush(Package $package)
    {
        $this->epm->getPackageManager()->flush($package);
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($type, $channel, array $message)
    {
        if (self::TYPE_SUBSCRIBE == $type) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function useCache($type, $channel, array $message)
    {
        return false;
    }
}
