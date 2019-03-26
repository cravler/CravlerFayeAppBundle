<?php

namespace Cravler\FayeAppBundle\EntryPoint;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class Example extends AbstractEntryPoint
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'faye-app.example';
    }

    /**
     * {@inheritdoc}
     */
    public function isGranted($type, $channel, array $message)
    {
        $user = null;
        if (isset($message['ext']['security'])) {
            $sm = $this->getEntryPointManager()->getSecurityManager();
            $user = $sm->getUser($message['ext']['security']);
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
