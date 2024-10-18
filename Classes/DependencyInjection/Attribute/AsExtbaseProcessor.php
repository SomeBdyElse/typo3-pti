<?php

namespace PrototypeIntegration\PrototypeIntegration\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AsExtbaseProcessor
{
    public function __construct(
        public string $controller,
        public string $action,
        public ?string $template = null,
    ) {
    }
}
