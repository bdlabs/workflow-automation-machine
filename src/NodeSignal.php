<?php

namespace DecisionMachine\FrameWork;

/**
 * Class NodeSignal
 *
 * @package DecisionMachine\FrameWork
 */
class NodeSignal
{
    protected string $sig = '';

    /**
     * @param array $data
     * @param $signalType
     */
    public function __construct(
        private readonly array $data,
        private $signalType,
    ) {
    }

    /**
     * @return array
     */
    public function valueOf(): array
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return ($this->signalType)();
    }

    /**
     * @param $signalType
     *
     * @return bool
     */
    public function equal($signalType): bool
    {
        return sprintf("%s", $signalType) === sprintf("%s", $this->signalType);
    }
}
