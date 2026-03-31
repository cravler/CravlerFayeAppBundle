<?php

namespace Cravler\FayeAppBundle\Package;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class Package
{
    public function __construct(
        private readonly mixed $body,
    ) {
    }

    public function __toString(): string
    {
        return \json_encode($this->body) ?: '{}';
    }
}
