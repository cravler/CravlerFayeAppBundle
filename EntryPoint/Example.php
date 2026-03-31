<?php

namespace Cravler\FayeAppBundle\EntryPoint;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class Example extends AbstractEntryPoint
{
    public function getId(): string
    {
        return 'faye-app.example';
    }

    public function isGranted(string $type, string $channel, array $message): bool
    {
        $user = null;
        if (\is_array($message['ext'] ?? null) && \is_array($message['ext']['security'])) {
            $sm = $this->getEntryPointManager()->getSecurityManager();
            $user = $sm->getUser($message['ext']['security']);
        }

        // check user permissions, if needed

        $parts = \explode('/', $channel);

        if (self::TYPE_SUBSCRIBE === $type) {
            if (\in_array($parts[\count($parts) - 1], ['*', '**'])) {
                return false;
            }

            if (\in_array($parts[1], ['baz'])) {
                return false;
            }

            return true;
        }

        if (\in_array($parts[1], ['foo', 'baz'])) {
            return true;
        }

        return false;
    }
}
