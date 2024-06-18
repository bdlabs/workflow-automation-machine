<?php

namespace DecisionMachine\Nodes;

use DecisionMachine\NodeSignal;
use DecisionMachine\SignalType;

class FakeNode extends Node
{
    public function input(NodeSignal $signal)
    {
        parent::input($signal->newSignal([], new SignalType('')));
    }
}