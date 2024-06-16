<?php

namespace DecisionMachine;

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

    public function input(NodeSignal $signal)
    {
        if($this->callBackNode) {
            parent::input($this->callBackNode->input($signal));

            return;
        }

        parent::input($signal);

    }
}
