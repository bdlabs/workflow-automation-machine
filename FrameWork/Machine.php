<?php

namespace DecisionMachine\FrameWork;

/**
 * Class Machine
 *
 * @package DecisionMachine\FrameWork
 */
class Machine
{
    /** @var Signal[] */
    private array $outputsSignals = [];

    /** @var NodeInterface[] $nodeList */
    private array $nodeList = [];

    /** @var SignalType[] */
    private array $joinedNodesMap = [];

    private array $logs = [];

    private array $emits = [];

    private array $dependencies = [];

    private Signal|null $signal = null;

    public function getInputs(string $nodeName): Signal
    {
        return $this->outputsSignals[$nodeName];
    }

    public function registerNode(
        string $nodeName,
        NodeInterface $node,
        array $dependencies = []
    ): void {
        $this->nodeList[$nodeName] = $node;
        $this->dependencies[$nodeName] = $dependencies;
    }

    public function run(Signal $inputSignal): Signal
    {
        $this->graphRender();
        $this->emit('start', $inputSignal);
        $this->emitStart();

        return $this->signal;
    }

    public function joinNodes(string $sendingNodeName, array $signals): void
    {
        $this->joinedNodesMap[$sendingNodeName] = $signals;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function prepareSignal(array $data, $signalType)
    {
        return new Signal(
            new NodeSignal($data, $signalType),
            $signalType,
            $this
        );
    }

    protected function emitStart()
    {
        $relations = [];
        $nodeNamePositionList = [];
        $nodeNamePositionList['start'] = 0;
        $position = 1;
        while ($sendingNodeName = array_shift($this->emits)) {
            $signals = $this->joinedNodesMap[$sendingNodeName] ?? [];
            foreach ($signals ?? [] as $nodeName => $exceptionSignalType) {
                $relations[] = [
                    'from' => $sendingNodeName,
                    'to' => $nodeName,
                    'exception' => $exceptionSignalType,
                    'dependencies' => [...$this->dependencies[$nodeName], $sendingNodeName],
                    'position' => $position,
                ];
                $nodeNamePositionList[$nodeName] = $position++;
                $this->emits[] = $nodeName;
            }
        }
        foreach ($relations as &$list) {
            if (count($list['dependencies'])) {
                foreach ($list['dependencies'] as &$dependencies) {
                    $dependencies = $nodeNamePositionList[$dependencies] + 1;
                }
                $list['dependencies'] = max($list['dependencies']);
                $list['position'] = $list['dependencies'];
                $nodeNamePositionList[$list['to']] = $list['dependencies'];
            } else {
                $list['dependencies'] = 0;
            }
        }
        usort(
            $relations,
            function ($recordA, $recordB) {
                if ($recordA['dependencies']) {
                    return $recordA['dependencies'] <=> $recordB['position'];
                }

                return $recordA['position'] <=> $recordB['position'];
            }
        );

        while ($record = array_shift($relations)) {
            $sendingNodeName = $record['from'];
            $nodeName = $record['to'];
            $exceptionSignalType = $record['exception'];
            $signal = $this->getInputs($sendingNodeName);
            $this->logs[] = [
                'signal' => $sendingNodeName,
                'input' => json_encode($signal->signal()->valueOf()),
            ];
            if ((!$exceptionSignalType instanceof \Closure && $signal->signal()->equal($exceptionSignalType)) ||
                $exceptionSignalType instanceof \Closure && $exceptionSignalType($signal)) {
                $this->logs[] = [
                    'run' => $nodeName,
                    'exceptionSignalType' => $exceptionSignalType::class,
                    'exist' => (bool)$this->nodeList[$nodeName],
                ];
                try {
                    $this->emit($nodeName, $this->nodeList[$nodeName]->process($signal));
                } catch (\Exception $exception) {
                    $this->logs[] = [
                        'error' => $nodeName,
                        'input' => $exception->getMessage(),
                    ];
                }
            }
        }
    }

    protected function emit(string $sendingNodeName, Signal $signal)
    {
        $this->emits[] = $sendingNodeName;
        $this->outputsSignals[$sendingNodeName] = $signal;
        $this->signal = $signal;
        $this->logs[] = [
            'signal' => $sendingNodeName,
            'action' => 'register output signal',
        ];
    }

    protected function checkDependenciesExist(string $sendingNodeName, string $nodeName): bool
    {
        $signals = $this->joinedNodesMap[$sendingNodeName] ?? [];
        $dependencies = $this->dependencies[$nodeName] ?? [];

        return count(array_diff($dependencies, $signals)) === 0;
    }

    protected function graphRender()
    {
        $graph = $this->graphRenderStart();
        $this->graphValidate($graph);
    }

    protected function graphRenderStart()
    {
        $used = [];
        $current = $start = new GraphNode('start');
        foreach ($this->joinedNodesMap as $signalNodeName => $nodes) {
            if ($signalNodeName === 'start') {
                continue;
            }
            $finding = $start->find($signalNodeName);
            if (!$finding) {
                $finding = new GraphNode($signalNodeName);
                $current->join($finding);
            }
            $current = $finding ?: $current;
            foreach ($nodes as $nodeName => $signal) {
                if (isset($used[$nodeName])) {
                    throw new \Exception($nodeName . ' has been used.');
                }
                $current->join(new GraphNode($nodeName));
                $used[$nodeName] = true;
            }

            $current = $start;
        }

        return $start;
    }

    protected function graphValidate(GraphNode $aa): void
    {
        foreach ($this->dependencies as $nodeName => $dependencies) {
            foreach ($dependencies as $dependency) {
                if ($aa->find($nodeName)->find($dependency)) {
                    throw new \Exception($nodeName . ' cannot be dependent on the next node "' . $dependency . '"  .');
                }
            }
        }
    }
}