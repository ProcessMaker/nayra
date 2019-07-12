# ProcessMaker Nayra

ProcessMaker Nayra is a package that provides base classes to implement a process execution engine.
This includes patterns to implement activities, events and gateways.

## How to execute a process

Load a BPMN definition
```
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load('files/ParallelGateway.bpmn');
```
![ParallelGateway diagram](/docs/diagrams/ParallelGateway.svg "ParallelGateway diagram")

Get a reference to the process
```
        $process = $bpmnRepository->getProcess('ParallelGateway');
```
Create a data storage
```
        $dataStore = $this->repository->createDataStore();
```
Create a process instance
```
        $instance = $this->engine->createExecutionInstance($process, $dataStore);
```
Trigger the start event
```
        $start = $bpmnRepository->getStartEvent('StartEvent');
        $start->start($instance);
```
![Start Event](/docs/diagrams/ParallelGateway_1.svg "Start Event")

Execute tokens and run to the next state
```
        $this->engine->runToNextState();
```
One token arrives to the first task
```
        $firstTask = $bpmnRepository->getScriptTask('start');
        $token = $firstTask->getTokens($instance)->item(0);
```
![First task](/docs/diagrams/ParallelGateway_2.svg "First task")

Complete the first task
```
        $startActivity->complete($token);
```
Execute tokens and run to the next state
```
        $this->engine->runToNextState();
```
One token arrives to the second task and one to the third task
```
        $secondTask = $bpmnRepository->getScriptTask('ScriptTask_1');
        $token1 = $secondTask->getTokens($instance)->item(0);
        $thirdTask = $bpmnRepository->getScriptTask('ScriptTask_2');
        $token2 = $thirdTask->getTokens($instance)->item(0);
```
![Second task and third task](/docs/diagrams/ParallelGateway_3.svg "Second task and third task")


## License

ProcessMaker Nayra is open-sourced software licensed under the [Apache 2.0](http://www.apache.org/licenses/LICENSE-2.0.html) license.
