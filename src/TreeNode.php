<?php
/**
 * Author: Łukasz Koc <lukasz.koc@rawlplug.com>
 * Date: 18.06.2024
 * Time: 13:28
 */

namespace Bdlabs\WorkflowAutomationMachine;

/**
 * Class TreeNode
 *
 * @package DecisionMachine\FrameWork
 */
class TreeNode
{
    /** @var self[] */
    protected array $lines = [];

    /** @var self | null */
    private ?self $parent = null;

    public function __construct(private readonly string $name)
    {
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return $this[]
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @return $this
     */
    public function parent(): self
    {
        return $this->parent;
    }

    /**
     * @param self $aa
     *
     * @return void
     */
    public function join(self $aa): void
    {
        $this->lines[] = $aa;
        $aa->parent = $this;
    }

    /**
     * @param string $targetName
     *
     * @return $this|null
     */
    public function find(string $targetName): ?self
    {
        foreach ($this->lines as $line) {
            $result = $line->find($targetName);
            if ($result) {
                return $result;
            }
        }

        if ($targetName === $this->name) {
            return $this;
        }

        return null;
    }
}
