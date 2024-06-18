<?php

namespace DecisionMachine;

use DecisionMachine\FrameWork\Signal;

interface ImputableNodeInterface
{
    public function input(Signal $signal): Signal;
}