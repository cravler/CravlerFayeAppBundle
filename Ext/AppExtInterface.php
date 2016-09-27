<?php

namespace Cravler\FayeAppBundle\Ext;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface AppExtInterface extends ContributorInterface
{
    /**
     * @return string
     */
    public function getAppExt();
}
