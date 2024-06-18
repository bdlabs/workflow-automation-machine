<?php

namespace DecisionMachine\Nodes;

use DecisionMachine\FrameWork\Signal;

interface ApiNodeInterface
{
    /**
     * @param array{url:string,method:string,body:string,headers:[] $config }
     * @param \DecisionMachine\FrameWork\Signal $signal
     *
     * @return array{url:string,method:string,body:string,headers:[]}
     */
    public function prepareConfig(array $config, Signal $signal): array;
}