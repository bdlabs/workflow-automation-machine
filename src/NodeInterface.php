<?php

namespace DecisionMachine\FrameWork;

/**
 * Interface NodeInterface
 *
 * @package DecisionMachine\FrameWork
 */
interface NodeInterface
{
    /**
     * @param Signal $signal
     *
     * @return Signal
     */
    public function process(Signal $signal): Signal;

    /**
     * @return array
     */
    public function expectedSignals(): array;

    /**
     * @param callable $emitter
     *
     * @return void
     */
    public function setEmitter(callable $emitter): void;
}
