<?php

namespace Cravler\FayeAppBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Cravler\FayeAppBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Cravler\FayeAppBundle\DependencyInjection\Compiler\EntryPointsCompilerPass;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class CravlerFayeAppBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new EntryPointsCompilerPass);
        $container->addCompilerPass(new GlobalVariablesCompilerPass);
    }
}
