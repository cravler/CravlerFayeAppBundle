<?php

namespace Cravler\FayeAppBundle\EntryPoint;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Example extends AbstractEntryPoint
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'faye-app.example';
    }

    /**
     * @param string $type
     * @param string $channel
     * @param array $ext
     * @return bool
     */
    public function isGranted($type, $channel, array $ext)
    {
        $user = null;
        if (isset($ext['security'])) {
            $sm = $this->getEntryPointManager()->getSecurityManager();
            $user = $sm->getUser($ext['security']);
        }

        // check user permissions, if needed

        $parts = explode('/', $channel);

        if (self::TYPE_SUBSCRIBE == $type) {
            if (in_array($parts[count($parts) - 1], array('*', '**'))) {
                return false;
            }

            if (in_array($parts[1], array('baz'))) {
                return false;
            }

            return true;
        } else {
            if (in_array($parts[1], array('foo', 'baz'))) {
                return true;
            }

            return false;
        }
    }
}
