<?php

namespace DecisionMachine;

interface NodeInterface
{
    public function input(NodeSignal $signal);

    public function expectedSignals(): array;

    public function setEmitter(callable $emitter);
}