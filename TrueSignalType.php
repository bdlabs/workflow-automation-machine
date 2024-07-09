<?php

namespace DecisionMachine;

class TrueSignalType
{
    public function __construct(public $signalId)
    {
    }

    public function __toString(): string
    {
        return $this::class;
    }
}