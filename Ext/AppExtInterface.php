<?php

namespace Cravler\FayeAppBundle\Ext;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface AppExtInterface extends ContributorInterface
{
    public function getAppExt(?string $connection = null): string;
}
