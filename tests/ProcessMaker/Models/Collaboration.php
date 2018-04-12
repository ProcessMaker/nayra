<?php

namespace ProcessMaker\Models;

use ProcessMaker\Nayra\Contracts\Bpmn\CollaborationInterface;
use ProcessMaker\Nayra\Bpmn\BaseTrait;

class Collaboration implements CollaborationInterface
{

    use BaseTrait;
    protected $conversations;
    protected $correlationKeys;

    public function getConversations()
    {
        
    }

    public function getCorrelationKeys()
    {

    }

    public function getFactory()
    {
        
    }

    public function getMessageFlows()
    {

    }

    public function getParticipants()
    {
        
    }

    public function getProperties()
    {

    }

    public function getProperty($name, $default = null)
    {
        
    }

    public function isClosed()
    {

    }

    public function setClosed($isClosed)
    {
        
    }

    public function setFactory(\ProcessMaker\Nayra\Contracts\Repositories\RepositoryFactoryInterface $factory)
    {

    }

    public function setProperties(array $properties)
    {
        
    }

    public function setProperty($name, $value)
    {

    }
}
