<?php

namespace DecisionMachine;

class NodeSignal
{
    protected $sig = '';

    public function __construct(private readonly array $data, private $signalType)
    {
    }

    public function valueOf()
    {
        return $this->data;
    }

    public function type(): string
    {
        return $this->signalType::class;
    }

    public function equal($signalType)
    {
        return $signalType::class === $this->signalType::class;
    }
}