<?php
/**
 * Author: Łukasz Koc <lukasz.koc@rawlplug.com>
 * Date: 17.07.2024
 * Time: 21:11
 */

namespace Bdlabs\WorkflowAutomationMachine;

/**
 * Class TreeProcessRunner
 *
 * @package DecisionMachine\FrameWork
 */
class TreeProcessRunner
{
    protected $execute = [];

    /**
     * @param $tree
     * @param array $poolNodes
     * @param $emmitFunction
     */
    public function __construct(private $tree, private array $poolNodes, private $emmitFunction)
    {
    }

    /**
     * @param Signal $inputSignal
     *
     * @return bool
     */
    public function process(Signal $inputSignal)
    {
        $status = true;
        $emit = $this->emmitFunction;
        /** @var TreeNode[] $stack */
        $stack = [];
        $stack[] = $this->tree;
        $attempts = 0;
        do {
            /** @var TreeNode[] $stackTmp */
            $stackTmp = [];
            while (!empty($stack)) {
                $currentNode = array_pop($stack);
                if ($this->waitForDependencies($currentNode->name())) {
                    $stackTmp[] = $currentNode;
                    continue;
                }
                try {
                    $inputSignal = $outputSignal = $this->processNode($currentNode->name(), $inputSignal);
                    $emit($currentNode->name(), $outputSignal);
                    $this->execute[$currentNode->name()] = true;
                    $nodeChildren = $this->getNodesForSignalType(
                        array_reverse($currentNode->lines()),
                        $this->poolNodes[$currentNode->name()]['joined'],
                        $outputSignal
                    );
                    /** @var TreeNode $node */
                    foreach ($nodeChildren as $node) {
                        $stack[] = $node;
                    }
                } catch (\Throwable $throwable) {
                    $status = false;
                }
            }

            if (count($stackTmp)) {
                $stack = $stackTmp;
            }
        } while (count($stackTmp) && ++$attempts < 10);
        if ($attempts>=10) {
            $status = false;
        }

        return $status;
    }

    /**
     * @param string $nodeName
     *
     * @return bool
     */
    protected function waitForDependencies(string $nodeName)
    {
        foreach ($this->poolNodes[$nodeName]['dependencies'] as $dependency) {
            if (!isset($this->execute[$dependency])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TreeNode[] $nodes
     * @param array $joined
     * @param Signal $signal
     *
     * @return \Generator
     */
    protected function getNodesForSignalType(array $nodes, array $joined, Signal $signal)
    {
        foreach ($nodes as $node) {
            $exceptionSignalType = $joined[$node->name()];
            if ((!$exceptionSignalType instanceof \Closure && $signal->signal()->equal($exceptionSignalType)) ||
                $exceptionSignalType instanceof \Closure && $exceptionSignalType($signal)) {
                yield $node;
            }
        }
    }

    /**
     * @param string $nodeName
     * @param Signal $inputSignal
     *
     * @return Signal
     */
    protected function processNode(string $nodeName, Signal $inputSignal): Signal
    {
        /** @var NodeInterface $node */
        $node = $this->poolNodes[$nodeName]['node'];

        return $node->process($inputSignal);
    }
}