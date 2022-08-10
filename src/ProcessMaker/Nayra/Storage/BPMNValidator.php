<?php

namespace ProcessMaker\Nayra\Storage;

use LibXMLError;
use ProcessMaker\Nayra\Contracts\Storage\BpmnDocumentInterface;

/**
 * Validate a BPMN document
 */
class BPMNValidator
{
    /**
     * @var BpmnDocument
     */
    private $document;

    /**
     * LIBXML errors
     *
     * @var array
     */
    private $errors = [];

    /**
     * Validation constructor
     *
     * @param \ProcessMaker\Nayra\Contracts\Storage\BpmnDocumentInterface $handler [description]
     */
    public function __construct(BpmnDocumentInterface $document)
    {
        $this->document = $document;
    }

    /**
     * @param \LibXMLError object $error
     *
     * @return string
     */
    private function libxmlDisplayError(LibXMLError $error)
    {
        $errorString = "Error $error->code (Line:{$error->line}):";
        $errorString .= trim($error->message);

        return $errorString;
    }

    /**
     * @return array
     */
    private function getLibxmlErrors()
    {
        $errors = libxml_get_errors();
        $result = [];
        foreach ($errors as $error) {
            $result[] = $this->libxmlDisplayError($error);
        }
        libxml_clear_errors();

        return $result;
    }

    /**
     * Validate Incoming Feeds against Listing Schema
     *
     * @param string $schema
     *
     * @return bool
     */
    public function validate($schema)
    {
        $this->errors = $this->getLibxmlErrors();
        $hasErrors = false;
        $validation = $this->document->schemaValidate($schema);
        if (!$validation) {
            $this->errors = array_merge($this->errors, $this->getLibxmlErrors());
            $hasErrors = true;
        }

        return !$hasErrors;
    }

    /**
     * Display Error if Resource is not validated
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
