<?php

namespace PrototypeIntegration\PrototypeIntegration\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class AsRecordProcessorCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $registryDefinition = $container->findDefinition('PrototypeIntegration\PrototypeIntegration\View\RecordProcessorRegistry');
            $factoryDefinition = $container->findDefinition('PrototypeIntegration\PrototypeIntegration\Processor\RecordProcessorFactory');
        } catch (ServiceNotFoundException $e) {
            return;
        }

        $processorReferences = [];
        foreach ($container->findTaggedServiceIds('pti.record_processors') as $processorClassName => $tags) {
            $processorReferences[$processorClassName] = new Reference($processorClassName);
            foreach ($tags as $attributes) {
                $registryDefinition->addMethodCall('addProcessor', [
                    $attributes['mainType'],
                    $attributes['recordType'],
                    $processorClassName,
                ]);
            }
        }

        $factoryDefinition->setArgument(
            '$processorLocator',
            ServiceLocatorTagPass::register($container, $processorReferences),
        );
    }
}
