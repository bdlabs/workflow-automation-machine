<?php

namespace DecisionMachine;

class FakeNode extends Node
{
    public function input(NodeSignal $signal)
    {
        parent::input(new NodeSignal([], new SignalType('')));
    }
}