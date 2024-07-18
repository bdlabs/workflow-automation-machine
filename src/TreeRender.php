<?php
/**
 * Author: Łukasz Koc <lukasz.koc@rawlplug.com>
 * Date: 17.07.2024
 * Time: 20:48
 */

namespace DecisionMachine\FrameWork;

/**
 * Class TreeRender
 *
 * @package DecisionMachine\FrameWork
 */
class TreeRender
{
    /**
     * @param TreeNode[] $poolNodes
     *
     * @return TreeNode
     * @throws \Exception
     */
    public function render(array $poolNodes)
    {
        /** @var SignalType[] $joinedNodesMap */
        $joinedNodesMap = [];
        $usedNodes = [];
        /** @var TreeNode[] $nodeList */
        $nodeList = [];
        $current = $startNode = new TreeNode('start');
        $nodeList['start'] = $startNode;
        foreach ($poolNodes as $nodeName => $node) {
            if ($nodeName !== 'start') {
                $nodeList[$nodeName] = new TreeNode($nodeName);
            }
            $joinedNodesMap[$nodeName] = $node['joined'];
        }
        //$relations = $this->prepareRelation();

        foreach ($joinedNodesMap as $signalNodeName => $nodes) {
            //echo $signalNodeName . PHP_EOL;
            $nodeRoot = &$nodeList[$signalNodeName];
            foreach ($nodes as $nodeName => $signal) {
                //echo '=> ' . $nodeName . ' join to ' .  $nodeRoot->name() . ' ' . ($nodeRoot->parent() ? $nodeRoot->parent()->name() : '-') . '<=' . PHP_EOL;
                if (isset($usedNodes[$nodeName])) {
                    throw new \Exception($nodeName . ' has been used.');
                }
                $nodeRoot->join($nodeList[$nodeName]);
                $usedNodes[$nodeName] = true;
            }
        }

        return $startNode;
    }
}
