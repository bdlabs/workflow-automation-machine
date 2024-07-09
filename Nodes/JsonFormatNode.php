<?php

namespace DecisionMachine\Nodes;

use DecisionMachine\FrameWork\Signal;
use DecisionMachine\ImputableNodeInterface;

class JsonFormatNode extends Node
{
    /**
     * @param ApiNodeInterface|null $callBackNode
     */
    public function __construct(private readonly ?ImputableNodeInterface $callBackNode = null)
    {
    }

    public function process(Signal $signal): Signal
    {
        if($this->callBackNode) {
            return $this->callBackNode->input($signal);
        }

        return $signal;
    }
}
