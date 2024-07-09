<?php

namespace DecisionMachine\FrameWork;

/**
 * Class Machine
 *
 * @package DecisionMachine\FrameWork
 */
class Machine
{
    private Logger $logger;

    private MachineNodesContainer $nodeContainer;

    private Signal|null $signal = null;

    /** @var Signal[] */
    private array $outputsSignals = [];

    private array $emits = [];

    public function __construct()
    {
        $this->logger = new Logger();
        $this->nodeContainer = new MachineNodesContainer();
        $this->registerNode('start', $this->getNode());
    }

    public function getInputs(string $nodeName): Signal
    {
        return $this->outputsSignals[$nodeName];
    }

    public function registerNode(
        string $nodeName,
        NodeInterface $node,
        array $dependencies = []
    ): void {
        $this->nodeContainer->add($nodeName, $node, $dependencies);
    }

    /**
     * @throws \Exception
     */
    public function run(Signal $inputSignal): Signal
    {
        $this->graphRender();
        $this->emit('start', $inputSignal);
        $this->emitStart();

        return $this->signal;
    }

    public function joinNodes(string $sendingNodeName, array $signals): void
    {
        foreach ($signals as $name => $signal) {
            $this->nodeContainer->join($sendingNodeName, $name, $signal);
        }
    }

    public function getLogs(): array
    {
        return $this->logger->logs();
    }

    public function prepareSignal(array $data, $signalType): Signal
    {
        return new Signal(
            new NodeSignal($data, $signalType),
            $signalType,
            $this
        );
    }

    /**
     * @return array
     */
    protected function prepareRelation(): array
    {
        return $this->nodeContainer->makeRelation($this->emits);
    }

    /**
     * @return void
     */
    protected function emitStart(): void
    {
        $relations = $this->prepareRelation();
        while ($record = array_shift($relations)) {
            $sendingNodeName = $record['from'];
            $nodeName = $record['to'];
            $exceptionSignalType = $record['exception'];
            $signal = $this->getInputs($sendingNodeName);
            $this->logger->init($sendingNodeName, json_encode($signal->signal()->valueOf()));
            if ((!$exceptionSignalType instanceof \Closure && $signal->signal()->equal($exceptionSignalType)) ||
                $exceptionSignalType instanceof \Closure && $exceptionSignalType($signal)) {
                $this->logger->run(
                    $sendingNodeName,
                    [
                        'run' => $nodeName,
                        'exceptionSignalType' => $exceptionSignalType::class,
                        'exist' => $this->nodeContainer->exist($nodeName),
                    ]
                );
                try {
                    $this->emit($nodeName, $this->nodeContainer->process($nodeName, $signal));
                } catch (\Exception $exception) {
                    $this->logger->error(
                        $sendingNodeName,
                        [
                            'run' => $nodeName,
                            'message' => $exception->getMessage(),
                        ]
                    );
                }
            }
        }
    }

    protected function emit(string $sendingNodeName, Signal $signal): void
    {
        $this->emits[] = $sendingNodeName;
        $this->outputsSignals[$sendingNodeName] = $signal;
        $this->signal = $signal;
        $this->logger->registerOutputSignal($sendingNodeName, ['message' => 'register output signal']);
    }

    /**
     * @throws \Exception
     */
    protected function graphRender(): void
    {
        $graph = $this->graphRenderStart();
        $this->graphValidate($graph);
    }

    /**
     * @throws \Exception
     */
    protected function graphRenderStart(): GraphNode
    {
        /** @var SignalType[] $joinedNodesMap */
        $joinedNodesMap = [];
        $used = [];
        $current = $start = new GraphNode('start');
        foreach ($this->nodeContainer->getAll() as $nodeName => $node) {
            $joinedNodesMap[$nodeName] = $node['joined'];
        }
        foreach ($joinedNodesMap as $signalNodeName => $nodes) {
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

    /**
     * @throws \Exception
     */
    protected function graphValidate(GraphNode $aa): void
    {
        $dependenciesTotal = [];
        foreach ($this->nodeContainer->getAll() as $nodeName => $node) {
            $dependenciesTotal[$nodeName] = $node['dependencies'];
        }
        foreach ($dependenciesTotal as $nodeName => $dependencies) {
            foreach ($dependencies as $dependency) {
                if ($aa->find($nodeName)->find($dependency)) {
                    throw new \Exception($nodeName . ' cannot be dependent on the next node "' . $dependency . '"  .');
                }
            }
        }
    }

    /**
     * @return NodeInterface
     */
    protected function getNode(): NodeInterface
    {
        return new class implements NodeInterface {
            public function __construct()
            {
            }

            public function process(Signal $signal): Signal
            {
                return $signal;
            }

            public function expectedSignals(): array
            {
                return [];
            }

            public function setEmitter($emitter): void
            {
                $this->emitter = $emitter;
            }
        };
    }
}
