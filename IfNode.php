<?php

namespace DecisionMachine;

class IfNode extends Node
{
    public function input(NodeSignal $signal)
    {

        if ($signal->valueOf()['a'] ?? false) {
            parent::input(new NodeSignal($signal->valueOf(), new TrueSignalType('')));

            return;
        }
        parent::input(new NodeSignal($signal->valueOf(), new FalseSignalType('')));

    }
}