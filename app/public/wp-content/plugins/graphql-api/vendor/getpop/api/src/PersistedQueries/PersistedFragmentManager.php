<?php

declare (strict_types=1);
namespace PoP\API\PersistedQueries;

use PoP\API\Schema\SchemaDefinition;
class PersistedFragmentManager implements \PoP\API\PersistedQueries\PersistedFragmentManagerInterface
{
    /**
     * @var array<string, string>
     */
    protected $persistedFragments = [];
    /**
     * @var array<string, array>
     */
    protected $persistedFragmentsForSchema = [];
    public function getPersistedFragments() : array
    {
        return $this->persistedFragments;
    }
    public function getPersistedFragmentsForSchema() : array
    {
        return $this->persistedFragmentsForSchema;
    }
    public function addPersistedFragment(string $fragmentName, string $fragmentResolution, ?string $description = null) : void
    {
        $this->persistedFragments[$fragmentName] = $fragmentResolution;
        $this->persistedFragmentsForSchema[$fragmentName] = [SchemaDefinition::ARGNAME_NAME => $fragmentName];
        if ($description) {
            $this->persistedFragmentsForSchema[$fragmentName][SchemaDefinition::ARGNAME_DESCRIPTION] = $description;
        }
        $this->persistedFragmentsForSchema[$fragmentName][SchemaDefinition::ARGNAME_FRAGMENT_RESOLUTION] = $fragmentResolution;
    }
}
