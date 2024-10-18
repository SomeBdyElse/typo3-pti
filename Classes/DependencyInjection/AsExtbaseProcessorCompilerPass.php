<?php

namespace PrototypeIntegration\PrototypeIntegration\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

class AsExtbaseProcessorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $definition = $container->findDefinition('PrototypeIntegration\PrototypeIntegration\View\ExtbaseProcessorRegistry');
        } catch (ServiceNotFoundException $e) {
            return;
        }

        foreach ($container->findTaggedServiceIds('pti.extbase_processors') as $processorClassname => $tags) {
            $container->getDefinition($processorClassname)->setPublic(true);
            foreach ($tags as $attributes) {
                $definition->addMethodCall('addOverride', [
                    $attributes['controller'],
                    $attributes['action'],
                    $processorClassname,
                    $attributes['template'],
                ]);
            }
        }
    }
}
