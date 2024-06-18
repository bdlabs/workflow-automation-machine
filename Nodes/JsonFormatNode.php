<?php

namespace DecisionMachine\Nodes;

use DecisionMachine\FrameWork\Signal;
use DecisionMachine\ImputableNodeInterface;

class JsonFormatNode extends Node
{
    /**
     * @param string $Id
     * @param ApiNodeInterface|null $callBackNode
     */
    public function __construct(string $Id, private readonly ?ImputableNodeInterface $callBackNode = null)
    {
        parent::__construct($Id);
    }

    public function process(Signal $signal): Signal
    {
        if($this->callBackNode) {
            return $this->callBackNode->input($signal);
        }

        return $signal;
    }
}
