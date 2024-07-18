<?php
/**
 * Author: Łukasz Koc <lukasz.koc@rawlplug.com>
 * Date: 09.07.2024
 * Time: 12:06
 */

namespace DecisionMachine\FrameWork;

/**
 * Class Logger
 *
 * @package DecisionMachine\FrameWork
 */
class Logger
{
    protected array $logs = [];

    /**
     * @param string $signalName
     * @param string $input
     *
     * @return void
     */
    public function init(string $signalName, string $input): void
    {
        $this->logs[] = [
            'signal' => $signalName,
            'action' => 'init',
            'actionData' => [
                'input' => $input,
            ],
        ];
    }

    /**
     * @param string $signalName
     * @param array $actionData
     *
     * @return void
     */
    public function run(string $signalName, array $actionData): void
    {
        $this->logs[] = [
            'signal' => $signalName,
            'action' => 'run',
            'actionData' => $actionData,
        ];
    }

    /**
     * @param string $signalName
     * @param array $actionData
     *
     * @return void
     */
    public function error(string $signalName, array $actionData): void
    {
        $this->logs[] = [
            'signal' => $signalName,
            'action' => 'error',
            'actionData' => $actionData,
        ];
    }

    /**
     * @param string $signalName
     * @param array $actionData
     *
     * @return void
     */
    public function registerOutputSignal(string $signalName, array $actionData): void
    {
        $this->logs[] = [
            'signal' => $signalName,
            'action' => 'registerOutputSignal',
            'actionData' => $actionData,
        ];
    }

    /**
     * @return array
     */
    public function logs(): array
    {
        return $this->logs;
    }
}
