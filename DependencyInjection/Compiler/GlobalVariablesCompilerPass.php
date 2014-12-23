<?php

namespace Cravler\FayeAppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Cravler\FayeAppBundle\DependencyInjection\CravlerFayeAppExtension;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class GlobalVariablesCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $def = $container->getDefinition('twig');
        $def->addMethodCall('addGlobal', array(
            str_replace('.', '_', CravlerFayeAppExtension::CONFIG_KEY),
            $container->getParameter(CravlerFayeAppExtension::CONFIG_KEY . '.app')
        ));
    }
}
