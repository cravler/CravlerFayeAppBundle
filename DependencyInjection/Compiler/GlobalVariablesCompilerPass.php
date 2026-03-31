<?php

namespace Cravler\FayeAppBundle\DependencyInjection\Compiler;

use Cravler\FayeAppBundle\DependencyInjection\CravlerFayeAppExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class GlobalVariablesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $def = $container->getDefinition('twig');
        $def->addMethodCall('addGlobal', [
            \str_replace('.', '_', CravlerFayeAppExtension::CONFIG_KEY),
            $container->getParameter(CravlerFayeAppExtension::CONFIG_KEY.'.app'),
        ]);
    }
}
