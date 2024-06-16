<?php

namespace DecisionMachine;

class Node implements NodeInterface
{
    private $emitter;

    public function __construct(private readonly string $Id)
    {
    }

    public function input(NodeSignal $signal)
    {
        // TODO: Implement input() method.

        $emitter = $this->emitter;
        $emitter($this->Id, $signal);
    }

    public function expectedSignals(): array
    {
        return [];
    }

    public function setEmitter($emitter)
    {
        $this->emitter = $emitter;
    }
}