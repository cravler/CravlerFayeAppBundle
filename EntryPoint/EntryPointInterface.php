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
     * @param array $data
     */
    public function publish($channel, $data = null);

    /**
     * @param string $type
     * @param string $channel
     * @param array $ext
     * @return bool
     */
    public function isGranted($type, $channel, array $ext);
}
