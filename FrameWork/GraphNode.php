<?php
/**
 * Author: Łukasz Koc <lukasz.koc@rawlplug.com>
 * Date: 18.06.2024
 * Time: 13:28
 */

namespace DecisionMachine\FrameWork;

/**
 * Class GraphNode
 *
 * @package DecisionMachine\FrameWork
 */
class GraphNode
{
    /** @var GraphNode[] */
    protected array $lines = [];

    /** @var GraphNode | null */
    private ?GraphNode $parent = null;

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
     * @return \DecisionMachine\FrameWork\GraphNode[]
     */
    public function lines(): array
    {
        return $this->lines;
    }

    /**
     * @return \DecisionMachine\FrameWork\GraphNode
     */
    public function parent(): GraphNode
    {
        return $this->parent;
    }

    /**
     * @param \DecisionMachine\FrameWork\GraphNode $aa
     *
     * @return void
     */
    public function join(GraphNode $aa): void
    {
        $this->lines[] = $aa;
        $aa->parent = $this;
    }

    /**
     * @param string $targetName
     *
     * @return \DecisionMachine\FrameWork\GraphNode|$this|null
     */
    public function find(string $targetName): ?GraphNode
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
