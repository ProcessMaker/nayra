<?php

namespace ProcessMaker\Nayra\Contracts\Storage;

/**
 * BPMN Element interface
 *
 *
 * @method string C14N( bool $exclusive, bool $with_comments, array $xpath, array $ns_prefixes )
 * @method string getAttribute( string $name )
 * @method string getAttributeNS( string $namespaceURI , string $localName )
 * @method string getNodePath( void )
 * @method string lookupNamespaceUri( string $prefix )
 * @method string lookupPrefix( string $namespaceURI )
 * @method DOMNode appendChild( DOMNode $newnode )
 * @method int C14NFile( string $uri, bool $exclusive = FALSE, bool $with_comments = FALSE, array $xpath, array $ns_prefixes )
 * @method DOMNode cloneNode( bool $deep)
 * @method DOMAttr getAttributeNode( string $name )
 * @method DOMAttr getAttributeNodeNS( string $namespaceURI , string $localName )
 * @method DOMNodeList getElementsByTagName( string $name )
 * @method DOMNodeList getElementsByTagNameNS( string $namespaceURI , string $localName )
 * @method int getLineNo( void )
 * @method DOMNode insertBefore( DOMNode $newnode [, DOMNode $refnode )
 * @method DOMNode removeChild( DOMNode $oldnode )
 * @method DOMNode replaceChild( DOMNode $newnode , DOMNode $oldnode )
 * @method DOMAttr setAttribute( string $name , string $value )
 * @method DOMAttr setAttributeNode( DOMAttr $attr )
 * @method DOMAttr setAttributeNodeNS( DOMAttr $attr )
 * @method bool hasAttribute( string $name )
 * @method bool hasAttributeNS( string $namespaceURI , string $localName )
 * @method bool hasAttributes( void )
 * @method bool hasChildNodes( void )
 * @method bool isDefaultNamespace( string $namespaceURI )
 * @method bool isSameNode( DOMNode $node )
 * @method bool isSupported( string $feature , string $version )
 * @method void normalize( void )
 * @method bool removeAttribute( string $name )
 * @method bool removeAttributeNode( DOMAttr $oldnode )
 * @method bool removeAttributeNS( string $namespaceURI , string $localName )
 * @method void setAttributeNS( string $namespaceURI , string $qualifiedName , string $value )
 * @method void setIdAttribute( string $name , bool $isId )
 * @method void setIdAttributeNode( DOMAttr $attr , bool $isId )
 * @method void setIdAttributeNS( string $namespaceURI , string $localName , bool $isId )
 */
interface BpmnElementInterface
{
    /**
     * Get instance of the BPMN element.
     *
     * @return \ProcessMaker\Nayra\Contracts\Bpmn\EntityInterface
     */
    public function getBpmnElementInstance();
}
