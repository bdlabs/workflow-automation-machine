<?php

namespace DecisionMachine\Nodes;

use DecisionMachine\FrameWork\NodeInterface;
use DecisionMachine\FrameWork\Signal;

class Node implements NodeInterface
{
    public function __construct()
    {
    }

    public function process(Signal $signal): Signal
    {
        return $signal;
    }

    public function expectedSignals(): array
    {
        return [];
    }

    public function setEmitter($emitter): void
    {
        $this->emitter = $emitter;
    }
}