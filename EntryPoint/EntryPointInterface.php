<?php

namespace Cravler\FayeAppBundle\EntryPoint;

use Cravler\FayeAppBundle\Package\Package;
use Cravler\FayeAppBundle\Service\EntryPointManager;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface EntryPointInterface
{
    const TYPE_PUBLISH   = 'publish';
    const TYPE_SUBSCRIBE = 'subscribe';

    /**
     * Technical name of entry-point.
     *
     * @return string
     */
    public function getId();

    /**
     * @param string $channel
     * @param mixed $data
     * @return Package
     */
    public function publish($channel, $data = null);

    /**
     * @param Package $package
     */
    public function flush(Package $package);

    /**
     * @param string $type
     * @param string $channel
     * @param array $message
     * @return bool
     */
    public function isGranted($type, $channel, array $message);

    /**
     * @param string $type
     * @param string $channel
     * @param array $message
     * @return bool|int
     */
    public function useCache($type, $channel, array $message);
}
