<?php

namespace Cravler\FayeAppBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Sergei Vizel <sergei.vizel@gmail.com>
 */
class EntryPointsCompilerPass implements CompilerPassInterface
{
    /**
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('cravler_faye_app.service.entry_points_chain')) {
            return;
        }

        $definition = $container->getDefinition(
            'cravler_faye_app.service.entry_points_chain'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'cravler_faye_app.entry_point'
        );
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addEntryPoint',
                array(new Reference($id))
            );
        }
    }
}
