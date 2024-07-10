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

    public function hasInputs(string $nodeName): bool
    {
        return isset($this->outputsSignals[$nodeName]);
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
        $tree = $this->treeRender();
        $this->emit('start', $inputSignal);
        $this->emitStart($tree);

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
        $emits = ['start'];

        return $this->nodeContainer->makeRelation($this->emits);
    }

    /**
     * @param TreeNode $tree
     *
     * @return void
     */
    protected function emitStart(TreeNode $tree): void
    {
        $relations = $this->prepareRelation();
        var_dump($relations);

        //$this->walkingForTree(
        //    $tree,
        //    function (TreeNode $tree) use ($relations) {
        //        //echo $tree->parent()->name() . ' ' . $tree->name() . PHP_EOL;
        //        $sendingNodeName = $tree->parent()->name();
        //        $nodeName = $tree->name();
        //        $record = $relations[$sendingNodeName];
        //            $exceptionSignalType = $record['exception'];
        //        $signal = $this->getInputs($sendingNodeName);
        //        $this->logger->init($sendingNodeName, json_encode($signal->signal()->valueOf()));
        //        if ((!$exceptionSignalType instanceof \Closure && $signal->signal()->equal($exceptionSignalType)) ||
        //            $exceptionSignalType instanceof \Closure && $exceptionSignalType($signal)) {
        //            $this->logger->run(
        //                $sendingNodeName,
        //                [
        //                    'run' => $nodeName,
        //                    'exceptionSignalType' => $exceptionSignalType::class,
        //                    'exist' => $this->nodeContainer->exist($nodeName),
        //                ]
        //            );
        //            try {
        //                $this->emit($nodeName, $this->nodeContainer->process($nodeName, $signal));
        //            } catch (\Exception $exception) {
        //                $this->logger->error(
        //                    $sendingNodeName,
        //                    [
        //                        'run' => $nodeName,
        //                        'message' => $exception->getMessage(),
        //                    ]
        //                );
        //            }
        //        }
        //    }
        //);
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

    /**
     * @param \DecisionMachine\FrameWork\TreeNode $tree
     * @param $callBack
     *
     * @return void
     */
    public function walkingForTree(TreeNode $tree, $callBack): void
    {
        $isDone = true;
        /** @var TreeNode[] $stack */
        $stack = [];
        $stack[] = $tree;
        while (!empty($stack)) {
            $stackTmp = [];
            $currentNode = array_pop($stack);
            foreach ($currentNode->lines() as $node) {
                if ($this->nodeContainer->isExecuted($node->name())) {
                    $stackTmp[] = $node;
                    continue;
                }
                $status = $callBack($node);
                if ($status) {
                    $stackTmp[] = $node;
                }
                $isDone &= $status;
            }
            $stack = array_merge($stack, array_reverse($stackTmp));
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
     * @return TreeNode
     * @throws \Exception
     */
    protected function treeRender(): TreeNode
    {
        $tree = $this->treeRenderStart();
        $this->treeValidate($tree);

        return $tree;
    }

    /**
     * @throws \Exception
     */
    protected function treeRenderStart(): TreeNode
    {
        /** @var SignalType[] $joinedNodesMap */
        $joinedNodesMap = [];
        $usedNodes = [];
        $current = $startNode = new TreeNode('start');
        foreach ($this->nodeContainer->getAll() as $nodeName => $node) {
            $joinedNodesMap[$nodeName] = $node['joined'];
        }
        foreach ($joinedNodesMap as $signalNodeName => $nodes) {
            if ($signalNodeName === 'start') {
                continue;
            }
            $finding = $startNode->find($signalNodeName);
            if (!$finding) {
                $finding = new TreeNode($signalNodeName);
                $current->join($finding);
            }
            $current = $finding ?: $current;
            foreach ($nodes as $nodeName => $signal) {
                if (isset($usedNodes[$nodeName])) {
                    throw new \Exception($nodeName . ' has been used.');
                }
                $current->join(new TreeNode($nodeName));
                $usedNodes[$nodeName] = true;
            }

            $current = $startNode;
        }

        return $startNode;
    }

    /**
     * @throws \Exception
     */
    protected function treeValidate(TreeNode $treeNode): void
    {
        $dependenciesTotal = [];
        foreach ($this->nodeContainer->getAll() as $nodeName => $node) {
            $dependenciesTotal[$nodeName] = $node['dependencies'];
        }
        foreach ($dependenciesTotal as $nodeName => $dependencies) {
            foreach ($dependencies as $dependency) {
                if ($treeNode->find($nodeName)->find($dependency)) {
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
            }
        };
    }
}
