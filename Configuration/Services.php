<?php

namespace TYPO3\CMS\Core;

use PrototypeIntegration\PrototypeIntegration\DependencyInjection\AsExtbaseProcessorCompilerPass;
use PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute\AsExtbaseProcessor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder->registerAttributeForAutoconfiguration(
        AsExtbaseProcessor::class,
        static function (ChildDefinition $definition, AsExtbaseProcessor $attribute, \Reflector $reflector): void {
            $definition->addTag(
                'pti.extbase_processors',
                [
                    'controller' => $attribute->controller,
                    'action' => $attribute->action,
                    'template' => $attribute->template,
                ]
            );
        }
    );

    $containerBuilder->addCompilerPass(new AsExtbaseProcessorCompilerPass());
};
