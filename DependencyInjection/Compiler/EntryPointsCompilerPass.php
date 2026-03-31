<?php

namespace Cravler\FayeAppBundle\DependencyInjection\Compiler;

use Cravler\FayeAppBundle\Service\EntryPointsChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class EntryPointsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(EntryPointsChain::class)) {
            return;
        }

        $definition = $container->getDefinition(EntryPointsChain::class);
        $taggedServices = $container->findTaggedServiceIds('cravler_faye_app.entry_point');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addEntryPoint',
                [new Reference($id)],
            );
        }
    }
}
