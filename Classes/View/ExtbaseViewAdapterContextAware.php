<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

interface ExtbaseViewAdapterContextAware
{
    public function setViewAdapterContext(ExtbaseViewAdapterContext $viewAdapterContext): void;
}
