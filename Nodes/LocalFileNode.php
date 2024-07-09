<?php

namespace DecisionMachine\Nodes;

use DecisionMachine\FrameWork\Signal;
use DecisionMachine\ImputableNodeInterface;

class LocalFileNode extends Node
{
    /**
     * @param ImputableNodeInterface $callBackNode
     */
    public function __construct(private readonly ImputableNodeInterface $callBackNode)
    {
    }

    public function process(Signal $signal): Signal
    {
        return $this->callBackNode->input($signal);
    }
}
