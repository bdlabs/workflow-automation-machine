<?php

namespace Bdlabs\WorkflowAutomationMachine;

/**
 * Class SignalType
 *
 * @package DecisionMachine\FrameWork
 */
class SignalType
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this::class;
    }
}
