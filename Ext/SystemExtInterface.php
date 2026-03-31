<?php

namespace Cravler\FayeAppBundle\Ext;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
interface SystemExtInterface extends ContributorInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getSystemExt(): array;
}
