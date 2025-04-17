<?php

namespace Tests\Feature\Engine;

use ProcessMaker\Nayra\Storage\BpmnDocument;
use DOMDocument;

/**
 * Test for handling HTML entities in BPMN files.
 */
class BpmnDocumentHtmlEntitiesTest extends EngineTestCase
{
    /**
     * Test the replaceHtmlEntities method directly.
     */
    public function testReplaceHtmlEntities()
    {
        // String with HTML entities
        $source = 'This is a text with &lt;html&gt; entities &amp; special chars like &quot;quotes&quot; and &apos;apostrophes&apos; and &nbsp;spaces.';

        // Expected result after replacement
        $expected = 'This is a text with &#60;html&#62; entities &#38; special chars like &#34;quotes&#34; and &#39;apostrophes&#39; and &#160;spaces.';

        // Test the static method
        $result = BpmnDocument::replaceHtmlEntities($source);

        // Assert the result matches the expected output
        $this->assertEquals($expected, $result);
    }

    /**
     * Test loadXML with HTML entities.
     */
    public function testLoadXmlWithHtmlEntities()
    {
        // Create a BPMN XML string with HTML entities in the documentation tag
        $bpmnXml = file_get_contents(__DIR__ . '/files/BpmnWithHtmlEntities.bpmn');

        // 1. First try to load with regular DOMDocument - should fail or produce incorrect results
        $regularDom = new DOMDocument();
        $regularLoaded = @$regularDom->loadXML($bpmnXml); // @ to suppress warnings

        // If it loads without errors, check that the content is different from what we expect
        if ($regularLoaded) {
            $startEventDoc = $regularDom->getElementsByTagName('documentation')->item(0);
            $originalContent = $startEventDoc ? $startEventDoc->textContent : '';

            // The content should be mangled or different from what we expect with proper entity handling
            $expectedContent = 'This contains <b>HTML</b> entities & special chars like "quotes" and \'apostrophes\' and  spaces.';
            $this->assertNotEquals($expectedContent, $originalContent, 'Standard DOMDocument should not correctly handle HTML entities');
        }

        // 2. Now load with BpmnDocument which should handle HTML entities correctly
        $bpmnDocument = new BpmnDocument();
        $bpmnDocument->setEngine($this->engine);
        $bpmnDocument->setFactory($this->repository);

        // Load the XML with HTML entities
        $result = $bpmnDocument->loadXML($bpmnXml);
        $this->assertTrue($result, 'BpmnDocument should successfully load the XML with HTML entities');

        // Verify that documentation tags contain correctly converted entities
        $startEventDoc = $bpmnDocument->getElementsByTagName('documentation')->item(0);
        $this->assertNotNull($startEventDoc, 'Documentation element should exist');

        // The text content should have the HTML entities properly converted
        $nbsp = "\xC2\xA0";
        $expectedContent = 'This contains <b>HTML</b> entities & special chars like "quotes" and \'apostrophes\' and ' . $nbsp . 'spaces.';
        $this->assertEquals($expectedContent, $startEventDoc->textContent, 'HTML entities should be correctly converted');

        // Check the second documentation tag too
        $taskDoc = $bpmnDocument->getElementsByTagName('documentation')->item(1);
        $this->assertNotNull($taskDoc, 'Second documentation element should exist');
        $expectedTaskContent = 'Another <strong>documentation</strong> with & entities.';
        $this->assertEquals($expectedTaskContent, $taskDoc->textContent, 'HTML entities in second documentation should be correctly converted');
    }

    /**
     * Test loading a complex BPMN with HTML entities in various places.
     */
    public function testLoadComplexBpmnWithHtmlEntities()
    {
        // Create a more complex BPMN with HTML entities in various attributes and text content
        $complexBpmnXml = file_get_contents(__DIR__ . '/files/BpmnWithComplexHtml.bpmn');

        // Load with BpmnDocument
        $bpmnDocument = new BpmnDocument();
        $bpmnDocument->setEngine($this->engine);
        $bpmnDocument->setFactory($this->repository);

        $result = $bpmnDocument->loadXML($complexBpmnXml);
        $this->assertTrue($result, 'BpmnDocument should successfully load complex XML with HTML entities');

        // Check process name attribute
        $process = $bpmnDocument->getElementsByTagName('process')->item(0);
        $this->assertEquals('Process & HTML entities', $process->getAttribute('name'), 'Process name attribute should have entities converted');

        // Check start event name attribute
        $startEvent = $bpmnDocument->getElementsByTagName('startEvent')->item(0);
        $this->assertEquals('Start <event>', $startEvent->getAttribute('name'), 'Start event name attribute should have entities converted');

        // Check documentation content
        $documentation = $startEvent->getElementsByTagName('documentation')->item(0);
        $this->assertEquals(
            'Documentation with <ul><li>HTML list</li></ul> and & "quotes"',
            $documentation->textContent,
            'Documentation should have entities converted'
        );

        // Check sequence flow name attribute
        $sequenceFlow = $bpmnDocument->getElementsByTagName('sequenceFlow')->item(0);
        $this->assertEquals(
            'Flow with & special "chars"',
            $sequenceFlow->getAttribute('name'),
            'Sequence flow name attribute should have entities converted'
        );
    }
}
