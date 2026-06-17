<?php

namespace PrototypeIntegration\PrototypeIntegration\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

class AsContentElementControllerCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        try {
            $registryDefinition = $container->findDefinition('PrototypeIntegration\PrototypeIntegration\View\ControllerRegistry');
            $factoryDefinition = $container->findDefinition('PrototypeIntegration\PrototypeIntegration\Processor\ControllerFactory');
        } catch (ServiceNotFoundException $e) {
            return;
        }

        $controllerReferences = [];
        foreach ($container->findTaggedServiceIds('pti.content_element_controllers') as $controllerClassName => $tags) {
            $controllerReferences[$controllerClassName] = new Reference($controllerClassName);
            foreach ($tags as $attributes) {
                $registryDefinition->addMethodCall('addController', [
                    $attributes['table'],
                    $attributes['type'],
                    $controllerClassName,
                ]);
            }
        }

        $factoryDefinition->setArgument(
            '$controllerLocator',
            ServiceLocatorTagPass::register($container, $controllerReferences),
        );
    }
}
