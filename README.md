# Workflow Automation Machine

## Introduction
The "Workflow Automation Machine" is an advanced project based on the **Workflow Automation pattern**,
which enables the automation of decision-making processes in various business domains.
With the "Workflow Automation Machine", companies can automate complex sequences of actions triggered based on
a set of business rules and decision logic.

## Purpose
The "Workflow Automation Machine" serves the following purposes:
- **Business Process Automation**: It allows the definition and execution of complex workflows that automatically carry out actions based on defined rules and conditions.
- **Enhancing Operational Efficiency**: By eliminating manual tasks and minimizing human errors, the "Workflow Automation Machine" enables the improvement of business process efficiency.
- **Rapid Response to Changes**: It allows for quick adaptation of processes and business rules to changing market and operational conditions.
- **Integration with Other Systems**: With its open architecture and capability to integrate with various systems and databases, the "Workflow Automation Machine" can be easily deployed in existing IT environments.

```php
@startuml
Machine *-- MachineNodesContainer
Machine *-- TreeNode
MachineNodesContainer o-- NodeInterface
Machine o-- Signal
Signal *-- NodeSignal

Client --> Machine
Client --> NodeInterface
Client --> SignalType

MachineNodesContainer o-- SignalType
@enduml
```

## How to use?
```php
    $machine = new Machine();
    $machine->registerNode('Node1', new \DecisionMachine\Nodes\Node());
    $machine->registerNode('Node2', new \DecisionMachine\Nodes\Node());
    $machine->joinNodes('start', [
        'Node1' => new \DecisionMachine\FrameWork\SignalType(),
        'Node2' => new \DecisionMachine\FrameWork\SignalType(),
    ]);
    $signal = $machine->run(
        $machine->prepareSignal(
            [123], new \DecisionMachine\FrameWork\SignalType()
        )
    );
```