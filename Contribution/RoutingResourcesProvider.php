<?php

namespace Cravler\FayeAppBundle\Contribution;

use Sli\ExpanderBundle\Ext\ContributorInterface;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class RoutingResourcesProvider implements ContributorInterface
{
    /**
     * @inheritDoc
     */
    public function getItems()
    {
        return array(
            '@CravlerFayeAppBundle/Resources/config/routing.yml'
        );
    }
}
