<?php

declare (strict_types=1);
namespace GraphQLByPoP\GraphQLServer\FieldResolvers\EmbeddableFields;

use PoP\API\ComponentConfiguration as APIComponentConfiguration;
trait EmbeddableFieldsFieldResolverTrait
{
    /**
     * Only use it when "embeddable fields" is enabled.
     *
     * Check on runtime (not via container) since this option can be
     * assigned to the Schema Configuration in the GraphQL API plugin.
     */
    public function isServiceEnabled() : bool
    {
        return APIComponentConfiguration::enableEmbeddableFields();
    }
}
