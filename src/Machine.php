<?php

namespace DecisionMachine\FrameWork;

use Exception;

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

    private bool $processStatus = false;

    public function __construct()
    {
        $this->logger = new Logger();
        $this->nodeContainer = new MachineNodesContainer();
        $this->registerNode('start', $this->getNode());
    }

    /**
     * @throws \Exception
     */
    public function getInputs(string $nodeName): Signal
    {
        if (!isset($this->outputsSignals[$nodeName])) {
            throw new Exception(
                sprintf('Signal not found for "%s" node', $nodeName)
            );
        }

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
     * @param Signal $inputSignal
     *
     * @return Signal
     * @throws \Exception
     */
    public function run(Signal $inputSignal): Signal
    {
        $this->processStatus = true;
        $tree = $this->treeRender();
        $this->outputsSignals['start'] = $inputSignal;
        $this->processStatus = (new TreeProcessRunner($tree, $this->nodeContainer->getAll(), function ($nodeName, $signal) {
            $this->emit($nodeName, $signal);
        }))->process($inputSignal);

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
     * @return bool
     */
    public function status(): bool
    {
        return $this->processStatus;
    }

    protected function emit(string $sendingNodeName, Signal $signal): void
    {
        $this->outputsSignals[$sendingNodeName] = $signal;
        $this->signal = $signal;
        $this->logger->registerOutputSignal(
            $sendingNodeName,
            [
                'message' => 'register output signal',
                'signal' => $signal->signal()->valueOf(),
            ]
        );
    }

    /**
     * @return TreeNode
     * @throws \Exception
     */
    protected function treeRender(): TreeNode
    {
        $tree = (new TreeRender())->render($this->nodeContainer->getAll());
        $this->treeValidate($tree);

        return $tree;
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
