<?php

namespace Bdlabs\WorkflowAutomationMachine;

/**
 * Class Signal
 *
 * @package DecisionMachine\FrameWork
 */
class Signal
{
    /**
     * @param NodeSignal $nodeSignal
     * @param $typeSignal
     * @param Machine $machine
     */
    public function __construct(
        private readonly NodeSignal $nodeSignal,
        private $typeSignal,
        private readonly Machine $machine
    ) {
    }

    /**
     * @return NodeSignal
     */
    public function signal(): NodeSignal
    {
        return $this->nodeSignal;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->typeSignal::class;
    }

    /**
     * @param array $data
     * @param $signalType
     *
     * @return Signal
     */
    public function prepareSignal(array $data, $signalType): Signal
    {
        return $this->machine->prepareSignal($data, $signalType);
    }

    /**
     * @param string $nodeName
     *
     * @return NodeSignal
     */
    public function getInputs(string $nodeName): NodeSignal
    {
        return $this->machine->getInputs($nodeName)->signal();
    }
}
