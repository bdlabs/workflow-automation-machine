<?php

namespace DecisionMachine\Nodes;

use DecisionMachine\FrameWork\Signal;
use DecisionMachine\ImputableNodeInterface;

class LocalFileNode extends Node
{
    /**
     * @param string $Id
     * @param ImputableNodeInterface $callBackNode
     */
    public function __construct(string $Id, private readonly ImputableNodeInterface $callBackNode)
    {
        parent::__construct($Id);
    }

    public function process(Signal $signal): Signal
    {
        return $this->callBackNode->input($signal);
    }
}
