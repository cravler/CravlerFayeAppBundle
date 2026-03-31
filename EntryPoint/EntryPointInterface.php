<?php

namespace Cravler\FayeAppBundle\EntryPoint;

use Cravler\FayeAppBundle\Package\Package;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
interface EntryPointInterface
{
    public const TYPE_PUBLISH = 'publish';
    public const TYPE_SUBSCRIBE = 'subscribe';

    /**
     * Technical name of entry-point.
     */
    public function getId(): string;

    public function publish(string $channel, mixed $data = null): Package;

    public function flush(Package $package): void;

    /**
     * @param array<string, mixed> $message
     */
    public function isGranted(string $type, string $channel, array $message): bool;

    /**
     * @param array<string, mixed> $message
     */
    public function useCache(string $type, string $channel, array $message): bool|int;
}
