<?php
/**
 * Author: Łukasz Koc <lukasz.koc@rawlplug.com>
 * Date: 09.07.2024
 * Time: 12:16
 */

namespace DecisionMachine\FrameWork;

/**
 * Class MachineNodesContainer
 *
 * @package DecisionMachine\FrameWork
 */
class MachineNodesContainer
{
    protected array $pool = [];

    /**
     * @param string $name
     * @param \DecisionMachine\FrameWork\NodeInterface $node
     * @param array $dependencies
     *
     * @return void
     */
    public function add(string $name, NodeInterface $node, array $dependencies): void
    {
        $this->pool[$name] = [
            'node' => $node,
            'dependencies' => $dependencies,
            'joined' => [],
        ];
    }

    /**
     * @param string $nodeNameParent
     * @param string $nodeName
     * @param $signalType
     *
     * @return void
     */
    public function join(string $nodeNameParent, string $nodeName, $signalType): void
    {
        $this->pool[$nodeNameParent]['joined'][$nodeName] = $signalType;
    }

    /**
     * @param array $emits
     *
     * @return array
     */
    public function makeRelation(array &$emits): array
    {
        return (new MachineNodeRelations())
            ->makeRelation($this->pool, $emits);
    }

    /**
     * @param string $nodeName
     *
     * @return bool
     */
    public function exist(string $nodeName): bool
    {
        return (bool)$this->pool[$nodeName];
    }

    /**
     * @param string $nodeName
     * @param Signal $signal
     *
     * @return Signal
     */
    public function process(string $nodeName, Signal $signal): Signal
    {
        return $this->pool[$nodeName]['node']->process($signal);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->pool;
    }
}