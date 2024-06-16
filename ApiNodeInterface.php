<?php

namespace DecisionMachine;

interface ApiNodeInterface
{
    public function prepareConfig(array $config, NodeSignal $signal);

    public function end(NodeSignal $signal): NodeSignal;
}