<?php

namespace Cravler\FayeAppBundle\DependencyInjection\Compiler;

use Cravler\FayeAppBundle\Service\ExtensionsChain;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Sergei Vizel
 *
 * @see https://github.com/cravler
 */
class ExtensionsCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(ExtensionsChain::class)) {
            return;
        }

        $definition = $container->getDefinition(ExtensionsChain::class);
        $taggedServices = $container->findTaggedServiceIds('cravler_faye_app.extension');

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addExtension',
                [new Reference($id)],
            );
        }
    }
}
