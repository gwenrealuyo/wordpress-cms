<?php

declare (strict_types=1);
namespace PoP\Definitions;

use PoP\Definitions\Configuration\Request;
use PoP\Definitions\Environment;
class DefinitionManager implements \PoP\Definitions\DefinitionManagerInterface
{
    /**
     * @var array<string, array>
     */
    protected $names = [];
    /**
     * @var array<string, array>
     */
    protected $name_definitions = [];
    /**
     * @var array<string, array>
     */
    protected $definition_names = [];
    /**
     * @var array<string, DefinitionResolverInterface>
     */
    protected $definition_resolvers = [];
    /**
     * @var \PoP\Definitions\DefinitionPersistenceInterface|null
     */
    protected $definition_persistence;
    public function isEnabled() : bool
    {
        return !Environment::disableDefinitions() && Request::isMangled();
    }
    /**
     * @return array<string, DefinitionResolverInterface>
     */
    public function getDefinitionResolvers() : array
    {
        if (!$this->isEnabled()) {
            return [];
        }
        return $this->definition_resolvers;
    }
    public function getDefinitionResolver(string $group) : ?\PoP\Definitions\DefinitionResolverInterface
    {
        if (!$this->isEnabled()) {
            return null;
        }
        return $this->definition_resolvers[$group] ?? null;
    }
    public function setDefinitionResolver(\PoP\Definitions\DefinitionResolverInterface $definition_resolver, string $group) : void
    {
        $this->definition_resolvers[$group] = $definition_resolver;
        // Allow the Resolver and the Persistence to talk to each other
        if ($this->definition_persistence) {
            $this->definition_persistence->setDefinitionResolver($definition_resolver, $group);
        }
    }
    public function getDefinitionPersistence() : ?\PoP\Definitions\DefinitionPersistenceInterface
    {
        if (!$this->isEnabled()) {
            return null;
        }
        return $this->definition_persistence;
    }
    public function setDefinitionPersistence(\PoP\Definitions\DefinitionPersistenceInterface $definition_persistence) : void
    {
        $this->definition_persistence = $definition_persistence;
        // Allow the Resolver and the Persistence to talk to each other
        foreach ($this->definition_resolvers as $group => $definition_resolver) {
            $this->definition_persistence->setDefinitionResolver($definition_resolver, $group);
        }
    }
    /**
     * Make sure the name has not been defined already. If it has, throw an Exception
     */
    public function getUniqueDefinition(string $name, string $group) : string
    {
        // If the ID has already been defined, then throw an Exception
        $this->names[$group] = $this->names[$group] ?? [];
        if (\in_array($name, $this->names[$group])) {
            throw new \Exception(\sprintf('Error with the Defining: another constant/object was already registered with name \'%s\' and group \'%s\'', $name, $group));
        }
        $this->names[$group][] = $name;
        // Simply return the definition
        return $this->getDefinition($name, $group);
    }
    /**
     * Function used to create a definition for a module.
     * Needed for reducing the filesize of the html generated for PROD
     * Instead of using the name of the $module, we use a unique number in base 36,
     * so the name will occupy much lower size
     * Comment Leo 27/09/2017: Changed from $module to only $id so that it can also
     * be used with ResourceLoaders
     */
    public function getDefinition(string $name, string $group) : string
    {
        if ($definition = isset($this->name_definitions[$group]) ? $this->name_definitions[$group][$name] : null) {
            return $definition;
        }
        // Allow the persistence layer to return the value directly
        $definitionPersistence = $this->getDefinitionPersistence();
        if ($definitionPersistence) {
            if ($definition = $definitionPersistence->getSavedDefinition($name, $group)) {
                $this->definition_names[$group][$definition] = $name;
                $this->name_definitions[$group][$name] = $definition;
                return $definition;
            }
        }
        // Allow the injected Resolver to decide how the name is resolved
        if ($definitionResolver = $this->getDefinitionResolver($group)) {
            $definition = $definitionResolver->getDefinition($name, $group);
            if ($definition != $name && $definitionPersistence) {
                $definitionPersistence->saveDefinition($definition, $name, $group);
            }
            $this->definition_names[$group][$definition] = $name;
            $this->name_definitions[$group][$name] = $definition;
            return $definition;
        }
        return $name;
    }
    /**
     * Given a definition, retrieve its original name
     */
    public function getOriginalName(string $definition, string $group) : string
    {
        // If it is cached in this object, return it already
        if (isset($this->definition_names[$group][$definition])) {
            return $this->definition_names[$group][$definition];
        }
        // Otherwise, ask if the persistence object has it
        if ($definitionPersistence = $this->getDefinitionPersistence()) {
            if ($name = $definitionPersistence->getOriginalName($definition, $group)) {
                $this->definition_names[$group][$definition] = $name;
                $this->name_definitions[$group][$name] = $definition;
                return $name;
            }
        }
        // It didn't find it, assume it's the same
        return $definition;
    }
    public function maybeStoreDefinitionsPersistently() : void
    {
        if ($definitionPersistence = $this->getDefinitionPersistence()) {
            $definitionPersistence->storeDefinitionsPersistently();
        }
    }
}
