<?php

namespace DecisionMachine;

interface ImputableNodeInterface
{
    public function input(NodeSignal $signal): NodeSignal;
}