<?php

namespace Tests\Feature\Patterns;

use Exception;
use ProcessMaker\Nayra\Contracts\Bpmn\ActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\CallActivityInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ErrorInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\IntermediateCatchEventInterface;
use ProcessMaker\Nayra\Contracts\Bpmn\ScriptTaskInterface;
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
    public function caseProvider()
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
     * @dataProvider caseProvider
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
            $this->runProcess($bpmnFile, $data, $startEvent->getAttribute('id'), $result, [], [], [], []);
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
            $output = isset($json['output']) ? $json['output'] : [];
            $errors = isset($json['errors']) ? $json['errors'] : [];
            $this->runProcess($bpmnFile, $json['data'], $json['startEvent'], $json['result'], $events, $output, $errors, $json);
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
     * @param mixed $output
     * @param array $errors
     *
     * @return void
     */
    private function runProcess($filename, $data, $startEvent, $result, $events, $output, $errors, $json)
    {
        $bpmnRepository = new BpmnDocument();
        $bpmnRepository->setEngine($this->engine);
        $bpmnRepository->setFactory($this->repository);
        $bpmnRepository->load($filename);
        $start = $bpmnRepository->getStartEvent($startEvent);
        $process = $start->getProcess();
        $dataStore = $this->repository->createDataStore();
        $dataStore->setData($data);
        // set global data storage
        $this->engine->setDataStore($dataStore);
        // create instance with initial data
        if ($start->getEventDefinitions()->count() > 0) {
            $start->execute($start->getEventDefinitions()->item(0));
            $instance = $process->getInstances()->count() ?  $process->getInstances()->item(0) : null;
        } else {
            $instance = $this->engine->createExecutionInstance($process, $dataStore);
            $start->start($instance);
        }
        $this->engine->runToNextState();
        $tasks = [];
        if (!$instance) {
            $this->assertEquals($result, $tasks);
            if ($output) {
                $this->assertEquals($output, $dataStore->getData());
            }
            return;
        }
        $tokens = $instance->getTokens();
        $processes = $bpmnRepository->getElementsByTagNameNS(BpmnDocument::BPMN_MODEL, 'process');
        $runtimeErrors = [];
        $this->engine->getDispatcher()->listen('ActivityException', function ($payload) use (&$runtimeErrors) {
            $error = $payload[1]->getProperty('error');
            if ($error) {
                $runtimeErrors[] = [
                    "element" => $payload[0]->getId(),
                    "error" => $error instanceof ErrorInterface ? $error->getId() : $error,
                ];
            }
        });
        while ($tokens->count()) {
            $submited = false;
            foreach ($processes as $process) {
                foreach ($process->getBpmnElementInstance()->getInstances() as $ins) {
                    foreach ($ins->getTokens() as $token) {
                        $element = $token->getOwnerElement();
                        $status = $token->getStatus();
                        if ($element instanceof ActivityInterface && !($element instanceof CallActivityInterface)
                            && $status === ActivityInterface::TOKEN_STATE_ACTIVE) {
                            $tasks[] = $element->getId();
                            if ($element instanceof ScriptTaskInterface && $element->getScriptFormat() === 'application/x-betsy') {
                                $element->runScript($token);
                            } else {
                                $element->complete($token);
                            }
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
                            $status = $token->getStatus();
                            $elements .= ' ' . $token->getOwnerElement()->getId() . ':' . $status;
                            if ($status == ActivityInterface::TOKEN_STATE_FAILING) {
                                $error = $token->getProperty('error');
                                $error = $error instanceof ErrorInterface ? $error->getId() : $error;
                                $runtimeErrors[] = [
                                    "element" => $token->getOwnerElement()->getId(),
                                    "error" => $error,
                                ];
                            }
                        }
                    }
                }
                break;
                //throw new Exception('The process got stuck in elements:' . $elements);
            }
        }
        $testName = $json['comment'] ?? '';
        $this->assertEquals($result, $tasks, $testName);
        if ($output) {
            $this->assertData($output, $dataStore->getData());
        }
        if ($errors) {
            $this->assertData($errors, $runtimeErrors);
        }
    }

    /**
     * Assert that $data contains the expected $subset
     *
     * @param mixed $subset
     * @param mixed $data
     * @param string $message
     * @param bool $skip
     *
     * @return mixed
     */
    private function assertData($subset, $data, $message = 'data', $skip = false)
    {
        if (!is_array($subset) || !is_array($data)) {
            if ($skip) {
                return $subset == $data;
            } else {
                return $this->assertEquals($subset, $data, "{$message} does not match " . \json_encode($subset));
            }
        }
        foreach ($subset as $key => $value) {
            if (substr($key, 0, 1) !== '*') {
                $this->assertData($value, $data[$key], "{$message}.{$key}");
                unset($subset[$key]);
                unset($data[$key]);
            }
        }
        foreach ($subset as $key => $value) {
            foreach ($data as $key1 => $value1) {
                if ($this->assertData($value, $value1, "{$message}.{$key}", true)) {
                    unset($subset[$key]);
                    unset($data[$key1]);
                    break;
                }
            }
        }
        if ($skip) {
            return count($subset) === 0;
        } else {
            $this->assertCount(0, $subset, "{$message} does not match");
        }
    }
}
