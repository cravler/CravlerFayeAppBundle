<?php

namespace Cravler\FayeAppBundle\Ext;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
interface AppExtInterface extends ContributorInterface
{
    public function getAppExt(?string $connection = null): string;
}
