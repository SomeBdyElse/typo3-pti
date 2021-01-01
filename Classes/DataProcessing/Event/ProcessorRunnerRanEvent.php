<?php

declare(strict_types=1);

namespace PrototypeIntegration\PrototypeIntegration\DataProcessing\Event;

class ProcessorRunnerRanEvent
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}
