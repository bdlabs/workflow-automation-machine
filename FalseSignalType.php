<?php

namespace DecisionMachine;

class FalseSignalType
{
    public function __construct(public $signalId)
    {
    }

    public function __toString(): string
    {
        return $this::class;
    }
}