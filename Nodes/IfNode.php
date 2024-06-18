<?php

namespace DecisionMachine\Nodes;

use DecisionMachine\FalseSignalType;
use DecisionMachine\NodeSignal;
use DecisionMachine\TrueSignalType;

class IfNode extends Node
{
    public function input(NodeSignal $signal)
    {

        if ($signal->valueOf()['a'] ?? false) {
            parent::input($signal->newSignal($signal->valueOf(), new TrueSignalType('')));

            return;
        }
        parent::input($signal->newSignal($signal->valueOf(), new FalseSignalType('')));

    }
}