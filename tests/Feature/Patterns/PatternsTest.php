<?php

namespace Tests\Feature\Patterns;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Storage\BpmnDocument;
use Tests\Feature\Engine\EngineTestCase;

/**
 * Tests for the ServiceTask element
 *
 */
class PatternsTest extends EngineTestCase
{
    private $basePath = __DIR__ . '/files/';

    /**
     * List the bpmn files
     *
     * @return array
     */
    public function testCaseProvider()
    {
        $data = [];
        foreach (glob($this->basePath . '*.bpmn') as $bpmnFile) {
            $data[] = [basename($bpmnFile)];
        }
        return $data;
    }

    /**
     * Tests the bpmn process completing all active tasks
     *
     * @param string $bpmnFile
     *
     * @dataProvider testCaseProvider
     */
    public function testProcessPatterns($bpmnFile)
    {
        $file = $this->basePath . $bpmnFile;
        $jsonFile = substr($file, 0, -4) . 'json';
        if (file_exists($jsonFile)) {
            $this->runProcessWithJson($jsonFile, $file);
        } else {
            $this->runProcessWithoutJson($file);
        }
    }

    /**
     * Run a process without json data
     *
     * @param string $bpmnFile
     *
     * @return void
     */
    private function runProcessWithoutJson($bpmnFile)
    {
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load($bpmnFile);
        $startEvents = $bpmnRepository->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'startEvent');
        foreach ($startEvents as $startEvent) {
            $data = [];
            $result = [];
            $this->runProcess($bpmnFile, $data, $startEvent->getAttribute('id'), $result, []);
        }
    }

    /**
     * Run a process with json data
     *
     * @param string $jsonFile
     * @param string $bpmnFile
     *
     * @return void
     */
    private function runProcessWithJson($jsonFile, $bpmnFile)
    {
        $tests = json_decode(file_get_contents($jsonFile), true);
        foreach ($tests as $json) {
            $events = isset($json['events']) ? $json['events'] : [];
            $this->runProcess($bpmnFile, $json['data'], $json['startEvent'], $json['result'], $events);
        }
    }

    /**
     * Run a process
     *
     * @param string $filename
     * @param array $data
     * @param string $startEvent
     * @param array $result
     * @param array $events
     *
     * @return void
     */
    private function runProcess($filename, $data, $startEvent, $result, $events)
    {
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load($filename);
        $start = $bpmnRepository->getStartEvent($startEvent);
        $process = $start->getProcess();
        $dataStore = $this->repository->createDataStore();
        $dataStore->setData($data);
        // create instance with initial data
        $instance = $this->engine->createExecutionInstance($process, $dataStore);
        // set global data storage
        $this->engine->setDataStore($dataStore);
        if ($start->getEventDefinitions()->count() > 0) {
            $start->execute($start->getEventDefinitions()->item(0), $instance);
        } else {
            $start->start($instance);
        }
        $this->engine->runToNextState();
        $tokens = $instance->getTokens();
        $tasks = [];
        $processes = $bpmnRepository->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'process');
        while ($tokens->count()) {
            $submited = false;
            foreach ($processes as $process) {
                foreach ($process->getBpmnElementInstance()->getInstances() as $ins) {
                    foreach ($ins->getTokens() as $token) {
                        $element = $token->getOwnerElement();
                        $status = $token->getStatus();
                        if ($element instanceof ActivityInterface && $status === ActivityInterface::TOKEN_STATE_ACTIVE) {
                            $tasks[] = $element->getId();
                            $element->complete($token);
                            $this->engine->runToNextState();
                            $submited = true;
                            break;
                        }
                        if ($element instanceof IntermediateCatchEventInterface && $status === IntermediateCatchEventInterface::TOKEN_STATE_ACTIVE) {
                            if ($events && $element->getId() === $events[0]) {
                                $eventDefinition = $element->getEventDefinitions()->item(0);
                                $element->execute($eventDefinition, $token->getInstance());
                                $this->engine->runToNextState();
                                $submited = true;
                                array_shift($events);
                            }
                        }
                    }
                }
            }
            $tokens = $instance->getTokens();
            if (!$submited && $tokens->count()) {
                $elements = '';
                foreach ($processes as $process) {
                    foreach ($process->getBpmnElementInstance()->getInstances() as $ins) {
                        foreach ($ins->getTokens() as $token) {
                            $elements .= ' ' . $token->getOwnerElement()->getId() . ':' . $token->getStatus();
                        }
                    }
                }
                throw new Exception('The process got stuck in elements:' . $elements);
            }
        }
        $this->assertEquals($result, $tasks);
    }
}
