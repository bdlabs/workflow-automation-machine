<?php

namespace DecisionMachine;

use DecisionMachine\FrameWork\SignalType;

/**
 * Class SwitchSignalType
 *
 * @package DecisionMachine
 */
class SwitchSignalType extends SignalType
{
    /**
     * @param string $value
     */
    public function __construct(private readonly string $value)
    {
    }

    public function __toString(): string
    {
        return $this::class . '_' . $this->value;
    }
}