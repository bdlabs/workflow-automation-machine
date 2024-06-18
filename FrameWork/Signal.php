<?php

namespace DecisionMachine\FrameWork;

class Signal
{
    public function __construct(
        private readonly NodeSignal $nodeSignal,
        private $typeSignal,
        private readonly Machine $machine
    ) {
    }

    public function signal(): NodeSignal
    {
        return $this->nodeSignal;
    }

    public function type(): string
    {
        return $this->typeSignal::class;
    }

    public function prepareSignal(array $data, $signalType): Signal
    {
        return $this->machine->prepareSignal($data, $signalType);
    }

    public function getInputs(string $nodeName): NodeSignal
    {
        return $this->machine->getInputs($nodeName)->signal();
    }
}
