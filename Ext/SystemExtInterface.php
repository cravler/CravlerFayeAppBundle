<?php

namespace Cravler\FayeAppBundle\Ext;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
interface SystemExtInterface extends ContributorInterface
{
    /**
     * @return array
     */
    public function getSystemExt();
}
