<?php

/**
 * Date: 23.11.15
 *
 * @author Portey Vasil <portey@gmail.com>
 */
namespace GraphQLByPoP\GraphQLParser\Execution;

use GraphQLByPoP\GraphQLParser\Exception\Parser\InvalidRequestException;
use GraphQLByPoP\GraphQLParser\Parser\Ast\ArgumentValue\Variable;
use GraphQLByPoP\GraphQLParser\Parser\Ast\ArgumentValue\VariableReference;
use GraphQLByPoP\GraphQLParser\Parser\Ast\Fragment;
use GraphQLByPoP\GraphQLParser\Parser\Ast\FragmentReference;
use GraphQLByPoP\GraphQLParser\Parser\Ast\Mutation;
use GraphQLByPoP\GraphQLParser\Parser\Ast\Query;
class Request
{
    /** @var  Query[] */
    private $queries = [];
    /** @var Fragment[] */
    private $fragments = [];
    /** @var Mutation[] */
    private $mutations = [];
    /** @var array */
    private $variables = [];
    /** @var VariableReference[] */
    private $variableReferences = [];
    /** @var  array */
    private $queryVariables = [];
    /** @var array */
    private $fragmentReferences = [];
    public function __construct($data = [], $variables = [])
    {
        if (\array_key_exists('queries', $data)) {
            $this->addQueries($data['queries']);
        }
        if (\array_key_exists('mutations', $data)) {
            $this->addMutations($data['mutations']);
        }
        if (\array_key_exists('fragments', $data)) {
            $this->addFragments($data['fragments']);
        }
        if (\array_key_exists('fragmentReferences', $data)) {
            $this->addFragmentReferences($data['fragmentReferences']);
        }
        if (\array_key_exists('variables', $data)) {
            $this->addQueryVariables($data['variables']);
        }
        if (\array_key_exists('variableReferences', $data)) {
            foreach ($data['variableReferences'] as $ref) {
                if (!\array_key_exists($ref->getName(), $variables)) {
                    /** @var Variable $variable */
                    $variable = $ref->getVariable();
                    /**
                     * If $variable is null, then it was not declared in the operation arguments
                     * @see https://graphql.org/learn/queries/#variables
                     */
                    if (\is_null($variable)) {
                        throw new InvalidRequestException(\sprintf("Variable %s hasn't been declared", $ref->getName()), $ref->getLocation());
                    }
                    if ($variable->hasDefaultValue()) {
                        $variables[$variable->getName()] = $variable->getDefaultValue()->getValue();
                        continue;
                    }
                    throw new InvalidRequestException(\sprintf("Variable %s hasn't been submitted", $ref->getName()), $ref->getLocation());
                }
            }
            $this->addVariableReferences($data['variableReferences']);
        }
        $this->setVariables($variables);
    }
    public function addQueries($queries)
    {
        foreach ($queries as $query) {
            $this->queries[] = $query;
        }
    }
    public function addMutations($mutations)
    {
        foreach ($mutations as $mutation) {
            $this->mutations[] = $mutation;
        }
    }
    public function addQueryVariables($queryVariables)
    {
        foreach ($queryVariables as $queryVariable) {
            $this->queryVariables[] = $queryVariable;
        }
    }
    public function addVariableReferences($variableReferences)
    {
        foreach ($variableReferences as $variableReference) {
            $this->variableReferences[] = $variableReference;
        }
    }
    public function addFragmentReferences($fragmentReferences)
    {
        foreach ($fragmentReferences as $fragmentReference) {
            $this->fragmentReferences[] = $fragmentReference;
        }
    }
    public function addFragments($fragments)
    {
        foreach ($fragments as $fragment) {
            $this->addFragment($fragment);
        }
    }
    /**
     * @return Query[]
     */
    public function getAllOperations()
    {
        return \array_merge($this->mutations, $this->queries);
    }
    /**
     * @return Query[]
     */
    public function getQueries()
    {
        return $this->queries;
    }
    /**
     * @return Fragment[]
     */
    public function getFragments()
    {
        return $this->fragments;
    }
    public function addFragment(Fragment $fragment)
    {
        $this->fragments[] = $fragment;
    }
    /**
     * @param $name
     *
     * @return null|Fragment
     */
    public function getFragment($name)
    {
        foreach ($this->fragments as $fragment) {
            if ($fragment->getName() == $name) {
                return $fragment;
            }
        }
        return null;
    }
    /**
     * @return Mutation[]
     */
    public function getMutations()
    {
        return $this->mutations;
    }
    /**
     * @return bool
     */
    public function hasQueries()
    {
        return (bool) \count($this->queries);
    }
    /**
     * @return bool
     */
    public function hasMutations()
    {
        return (bool) \count($this->mutations);
    }
    /**
     * @return bool
     */
    public function hasFragments()
    {
        return (bool) \count($this->fragments);
    }
    /**
     * @return array
     */
    public function getVariables()
    {
        return $this->variables;
    }
    /**
     * @return $this
     * @param mixed[]|string $variables
     */
    public function setVariables($variables)
    {
        if (!\is_array($variables)) {
            $variables = \json_decode($variables, \true);
        }
        $this->variables = $variables;
        foreach ($this->variableReferences as $reference) {
            /** invalid request with no variable */
            if (!$reference->getVariable()) {
                continue;
            }
            $variableName = $reference->getVariable()->getName();
            /** no variable was set at the time */
            if (!\array_key_exists($variableName, $variables)) {
                continue;
            }
            $reference->getVariable()->setValue($variables[$variableName]);
            $reference->setValue($variables[$variableName]);
        }
        return $this;
    }
    public function getVariable($name)
    {
        return $this->hasVariable($name) ? $this->variables[$name] : null;
    }
    public function hasVariable($name)
    {
        return \array_key_exists($name, $this->variables);
    }
    /**
     * @return array|Variable[]
     */
    public function getQueryVariables()
    {
        return $this->queryVariables;
    }
    /**
     * @param array $queryVariables
     */
    public function setQueryVariables($queryVariables)
    {
        $this->queryVariables = $queryVariables;
    }
    /**
     * @return array|FragmentReference[]
     */
    public function getFragmentReferences()
    {
        return $this->fragmentReferences;
    }
    /**
     * @param array $fragmentReferences
     */
    public function setFragmentReferences($fragmentReferences)
    {
        $this->fragmentReferences = $fragmentReferences;
    }
    /**
     * @return array|VariableReference[]
     */
    public function getVariableReferences()
    {
        return $this->variableReferences;
    }
}
