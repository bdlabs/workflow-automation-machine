<?php

namespace DecisionMachine;

class NodeSignalIfFalse extends NodeSignal
{
    protected $sig = false;

    public function equal(NodeSignal $nodeSignal)
    {
        return $nodeSignal->sig === $this->sig;
    }
}