<?php

namespace DecisionMachine\FrameWork;

interface NodeInterface
{
    public function process(Signal $signal): Signal;

    public function expectedSignals(): array;

    public function setEmitter(callable $emitter);
}