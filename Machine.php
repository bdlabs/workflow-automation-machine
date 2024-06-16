<?php

namespace DecisionMachine;

class Machine
{
    static private $outputsMemory = [];

    /** @var Node[] $nodeList */
    private array $nodeList = [];

    /** @var SignalType[] */
    private array $signals = [];

    private array $logs = [];

    public static function getInputs(string $nodeName): NodeSignal {
        return self::$outputsMemory[$nodeName];
    }

    public function registerNode(string $nodeName, NodeInterface $node)
    {
        $this->nodeList[$nodeName] = $node;
        $node->setEmitter(function ($signalId, $data) {
            $this->emit($signalId, $data);
        });
    }

    public function run(NodeSignal $inputSignal)
    {
        $this->emit('start', $inputSignal);
    }

    public function registerSignal(string $sendingNodeName, array $signals)
    {
        $this->signals[$sendingNodeName] = $signals;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    protected function emit(string $sendingNodeName, NodeSignal $signal)
    {
        $this->logs[] = [
            'signal' => $sendingNodeName,
            'input' => json_encode($signal->valueOf())
        ];
        $signals = $this->signals[$sendingNodeName] ?? [];
        self::$outputsMemory[$sendingNodeName] = $signal;

        foreach ($signals ?? [] as $nodeName => $exceptionSignalType) {
            if ((!$exceptionSignalType instanceof \Closure && $signal->equal($exceptionSignalType)) ||
                $exceptionSignalType instanceof \Closure && $exceptionSignalType($signal)) {
                $this->logs[] = [
                    'run' => $nodeName,
                    'exceptionSignalType' => $exceptionSignalType::class,
                ];
                $this->nodeList[$nodeName]->input($signal);
            }
        }
    }
}