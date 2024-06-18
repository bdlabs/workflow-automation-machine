<?php

namespace DecisionMachine\FrameWork;

class SignalType
{
    public function __toString(): string
    {
        return $this::class;
    }
}