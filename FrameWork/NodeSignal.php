<?php

namespace DecisionMachine\FrameWork;

class NodeSignal
{
    protected $sig = '';

    public function __construct(
        private readonly array $data,
        private $signalType,
    ) {
    }

    public function valueOf()
    {
        return $this->data;
    }

    public function type(): string
    {
        return ($this->signalType)();
    }

    public function equal($signalType)
    {
        return sprintf("%s", $signalType) === sprintf("%s", $this->signalType);
    }
}