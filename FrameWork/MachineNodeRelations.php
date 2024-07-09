<?php

namespace DecisionMachine\FrameWork;

/**
 * Class MachineNodeRelations
 *
 * @package DecisionMachine\FrameWork
 */
class MachineNodeRelations
{
    /**
     * @param array $pool
     * @param array $emits
     *
     * @return array
     */
    public function makeRelation(array $pool, array &$emits): array
    {
        $relations = [];
        $nodeNamePositionList = [];
        $nodeNamePositionList['start'] = 0;
        $position = 1;
        while ($sendingNodeName = array_shift($emits)) {
            $signals = $pool[$sendingNodeName]['joined'];
            foreach ($signals ?? [] as $nodeName => $exceptionSignalType) {
                $relations[] = [
                    'from' => $sendingNodeName,
                    'to' => $nodeName,
                    'exception' => $exceptionSignalType,
                    'dependencies' => [...$pool[$sendingNodeName]['dependencies'], $sendingNodeName],
                    'position' => $position,
                ];
                $nodeNamePositionList[$nodeName] = $position++;
                $emits[] = $nodeName;
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

        return $relations;
    }
}
