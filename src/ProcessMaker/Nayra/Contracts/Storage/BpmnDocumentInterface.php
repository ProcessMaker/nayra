<?php

namespace ProcessMaker\Nayra\Contracts\Storage;

use ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface;
use ProcessMaker\Nayra\Contracts\Repositories\StorageInterface;

/**
 * BPMN DOM file interface
 *
 *
 * @method DOMNode appendChild ( DOMNode $newnode )
 * @method string C14N ( bool $exclusive , bool $with_comments , array $xpath , array $ns_prefixes  )
 * @method int C14NFile ( string $uri , bool $exclusive = FALSE , bool $with_comments = FALSE , array $xpath , array $ns_prefixes  )
 * @method DOMNode cloneNode ( bool $deep  )
 * @method DOMAttr createAttribute ( string $name )
 * @method DOMAttr createAttributeNS ( string $namespaceURI , string $qualifiedName )
 * @method DOMCDATASection createCDATASection ( string $data )
 * @method DOMComment createComment ( string $data )
 * @method DOMDocumentFragment createDocumentFragment ( void )
 * @method DOMElement createElement ( string $name , string $value  )
 * @method DOMElement createElementNS ( string $namespaceURI , string $qualifiedName , string $value  )
 * @method DOMEntityReference createEntityReference ( string $name )
 * @method DOMProcessingInstruction createProcessingInstruction ( string $target , string $data  )
 * @method DOMText createTextNode ( string $content )
 * @method DOMElement getElementById ( string $elementId )
 * @method DOMNodeList getElementsByTagName ( string $name )
 * @method DOMNodeList getElementsByTagNameNS ( string $namespaceURI , string $localName )
 * @method int getLineNo ( void )
 * @method string getNodePath ( void )
 * @method bool hasAttributes ( void )
 * @method bool hasChildNodes ( void )
 * @method DOMNode importNode ( DOMNode $importedNode , bool $deep = FALSE  )
 * @method DOMNode insertBefore ( DOMNode $newnode , DOMNode $refnode  )
 * @method bool isDefaultNamespace ( string $namespaceURI )
 * @method bool isSameNode ( DOMNode $node )
 * @method bool isSupported ( string $feature , string $version )
 * @method mixed load ( string $filename , int $options = 0  )
 * @method bool loadHTML ( string $source , int $options = 0  )
 * @method bool loadHTMLFile ( string $filename , int $options = 0  )
 * @method mixed loadXML ( string $source , int $options = 0  )
 * @method string lookupNamespaceUri ( string $prefix )
 * @method string lookupPrefix ( string $namespaceURI )
 * @method void normalize ( void )
 * @method void normalizeDocument ( void )
 * @method bool registerNodeClass ( string $baseclass , string $extendedclass )
 * @method bool relaxNGValidate ( string $filename )
 * @method bool relaxNGValidateSource ( string $source )
 * @method DOMNode removeChild ( DOMNode $oldnode )
 * @method DOMNode replaceChild ( DOMNode $newnode , DOMNode $oldnode )
 * @method int save ( string $filename , int $options = 0  )
 * @method string saveHTML ( DOMNode $node = NULL  )
 * @method int saveHTMLFile ( string $filename )
 * @method string saveXML ( DOMNode $node , int $options = 0  )
 * @method bool schemaValidate ( string $filename , int $flags = 0  )
 * @method bool schemaValidateSource ( string $source , int $flags  )
 * @method bool validate ( void )
 * @method int xinclude ( int $options = 0  )
 */
interface BpmnDocumentInterface extends StorageInterface
{
    /**
     * Get the BPMN elements mapping.
     *
     * @return array
     */
    public function getBpmnElementsMapping();

    /**
     * Set a BPMN element mapping.
     *
     * @param string $namespace
     * @param string $tagName
     * @param mixed $mapping
     *
     * @return $this
     */
    public function setBpmnElementMapping($namespace, $tagName, $mapping);

    /**
     * Find a element by id.
     *
     * @param string $id
     *
     * @return BpmnElement
     */
    public function findElementById($id);

    /**
     * Index a BPMN element by id.
     *
     * @param string $id
     * @param EntityInterface $bpmn
     */
    public function indexBpmnElement($id, EntityInterface $bpmn);

    /**
     * Verify if the BPMN element identified by id was previously loaded.
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasBpmnInstance($id);

    /**
     * Get a BPMN element by id.
     *
     * @param string $id
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function getElementInstanceById($id);

    /**
     * Return true if the element instance exists in the Process.
     *
     * @param string $id
     *
     * @return bool
     */
    public function hasElementInstance($id);

    /**
     * Get skipElementsNotImplemented property.
     *
     * If set to TRUE, skip loading elements that are not implemented
     * If set to FALSE, throw ElementNotImplementedException
     *
     * @return bool
     */
    public function getSkipElementsNotImplemented();

    /**
     * Set skipElementsNotImplemented property.
     *
     * If set to TRUE, skip loading elements that are not implemented
     * If set to FALSE, throw ElementNotImplementedException
     *
     * @param bool $skipElementsNotImplemented
     *
     * @return BpmnDocument
     */
    public function setSkipElementsNotImplemented($skipElementsNotImplemented);

    /**
     * Validate document with BPMN schemas.
     *
     * @param string $schema
     */
    public function validateBPMNSchema($schema);

    /**
     * Get BPMN validation errors.
     *
     * @return array
     */
    public function getValidationErrors();
}
