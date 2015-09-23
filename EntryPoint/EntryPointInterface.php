<?php

namespace Cravler\FayeAppBundle\EntryPoint;

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
     */
    public function publish($channel, $data = null);

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
