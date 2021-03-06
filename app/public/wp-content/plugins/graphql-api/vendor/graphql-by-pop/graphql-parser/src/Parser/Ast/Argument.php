<?php

/**
 * Date: 23.11.15
 *
 * @author Portey Vasil <portey@gmail.com>
 */
namespace GraphQLByPoP\GraphQLParser\Parser\Ast;

use GraphQLByPoP\GraphQLParser\Parser\Ast\Interfaces\ValueInterface;
use GraphQLByPoP\GraphQLParser\Parser\Location;
class Argument extends \GraphQLByPoP\GraphQLParser\Parser\Ast\AbstractAst
{
    /** @var string */
    private $name;
    /** @var ValueInterface */
    private $value;
    /**
     * @param string         $name
     */
    public function __construct($name, ValueInterface $value, Location $location)
    {
        parent::__construct($location);
        $this->name = $name;
        $this->value = $value;
    }
    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
    /**
     * @return \GraphQLByPoP\GraphQLParser\Parser\Ast\Interfaces\ValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }
    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
