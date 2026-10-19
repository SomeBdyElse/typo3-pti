<?php

namespace TYPO3\CMS\Core;

use PrototypeIntegration\PrototypeIntegration\DependencyInjection\AsExtbaseProcessorCompilerPass;
use PrototypeIntegration\PrototypeIntegration\DependencyInjection\AsRecordProcessorCompilerPass;
use PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute\AsExtbaseProcessor;
use PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute\AsRecordProcessor;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container, ContainerBuilder $containerBuilder) {
    $containerBuilder->registerAttributeForAutoconfiguration(
        AsExtbaseProcessor::class,
        static function (ChildDefinition $definition, AsExtbaseProcessor $attribute, \Reflector $reflector): void {
            $definition->addTag(
                'pti.extbase_processors',
                array_filter([
                    'controller' => $attribute->controller,
                    'action' => $attribute->action,
                    'template' => $attribute->template,
                    'adapterClassName' => $attribute->adapterClassName,
                ], fn (?string $value): bool => isset($value))
            );
        }
    );

    $containerBuilder->registerAttributeForAutoconfiguration(
        AsRecordProcessor::class,
        static function (ChildDefinition $definition, AsRecordProcessor $attribute, \Reflector $reflector): void {
            $definition->addTag(
                'pti.record_processors',
                [
                    'mainType' => $attribute->mainType,
                    'recordType' => $attribute->recordType,
                ]
            );
        }
    );

    $containerBuilder->addCompilerPass(new AsExtbaseProcessorCompilerPass());
    $containerBuilder->addCompilerPass(new AsRecordProcessorCompilerPass());
};
