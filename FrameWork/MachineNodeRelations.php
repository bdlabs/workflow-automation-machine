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
                    'dependencies' => $pool[$nodeName]['dependencies'],
                    'dependenciesAll' => [...$pool[$nodeName]['dependencies'], $sendingNodeName],
                    'position' => $position,
                ];
                $nodeNamePositionList[$nodeName] = $position++;
                $emits[] = $nodeName;
            }
        }
        foreach ($relations as &$list) {
            if (count($list['dependenciesAll'])) {
                foreach ($list['dependenciesAll'] as &$dependencies) {
                    $dependencies = $nodeNamePositionList[$dependencies] + 1;
                }
                $list['dependenciesAll'] = max($list['dependenciesAll']);
                $list['position'] = $list['dependenciesAll'];
                $nodeNamePositionList[$list['to']] = $list['dependenciesAll'];
            } else {
                $list['dependenciesAll'] = 0;
            }
        }
        usort(
            $relations,
            function ($recordA, $recordB) {
                if ($recordA['dependenciesAll']) {
                    return $recordA['dependenciesAll'] <=> $recordB['position'];
                }

                return $recordA['position'] <=> $recordB['position'];
            }
        );

        return $relations;
    }
}
