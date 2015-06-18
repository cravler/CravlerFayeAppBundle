<?php

namespace Cravler\FayeAppBundle\EntryPoint;

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
     * @param string $channel
     * @param mixed $data
     */
    public function publish($channel, $data = null)
    {
        $this->epm->publish($this, $channel, $data);
    }

    /**
     * @param string $type
     * @param string $channel
     * @param array $message
     * @return bool
     */
    public function isGranted($type, $channel, array $message)
    {
        if (self::TYPE_SUBSCRIBE == $type) {
            return true;
        } else {
            return false;
        }
    }
}
