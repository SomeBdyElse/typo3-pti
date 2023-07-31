<?php

namespace PrototypeIntegration\PrototypeIntegration\View;

interface ViewAdapterContextAware
{
    public function setViewAdapterContext(ExtbaseViewAdapterContext $viewAdapterContext): void;
}
