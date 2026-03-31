<?php

namespace Cravler\FayeAppBundle;

use Cravler\FayeAppBundle\DependencyInjection\Compiler\EntryPointsCompilerPass;
use Cravler\FayeAppBundle\DependencyInjection\Compiler\ExtensionsCompilerPass;
use Cravler\FayeAppBundle\DependencyInjection\Compiler\GlobalVariablesCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class CravlerFayeAppBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ExtensionsCompilerPass());
        $container->addCompilerPass(new EntryPointsCompilerPass());
        $container->addCompilerPass(new GlobalVariablesCompilerPass());
    }
}
