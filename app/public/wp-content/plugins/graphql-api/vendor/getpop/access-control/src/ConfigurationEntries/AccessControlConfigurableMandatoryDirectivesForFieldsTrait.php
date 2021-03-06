<?php

declare (strict_types=1);
namespace PoP\AccessControl\ConfigurationEntries;

use PoP\AccessControl\ComponentConfiguration;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\MandatoryDirectivesByConfiguration\ConfigurationEntries\ConfigurableMandatoryDirectivesForFieldsTrait;
trait AccessControlConfigurableMandatoryDirectivesForFieldsTrait
{
    use ConfigurableMandatoryDirectivesForFieldsTrait {
        ConfigurableMandatoryDirectivesForFieldsTrait::getMatchingEntries as getUpstreamMatchingEntries;
    }
    use AccessControlConfigurableMandatoryDirectivesForItemsTrait;
    /**
     * Filter all the entries from the list which apply to the passed typeResolver and fieldName
     */
    protected final function getMatchingEntries(array $entryList, TypeResolverInterface $typeResolver, array $fieldInterfaceResolverClasses, string $fieldName) : array
    {
        /**
         * If enabling individual control over public/private schema modes, then we must also check
         * that this field has the required mode.
         * If the schema mode was not defined in the entry, then this field is valid if the default
         * schema mode is the same required one
         */
        if (!ComponentConfiguration::enableIndividualControlForPublicPrivateSchemaMode()) {
            return $this->getUpstreamMatchingEntries($entryList, $typeResolver, $fieldInterfaceResolverClasses, $fieldName);
        }
        $typeResolverClass = \get_class($typeResolver);
        $individualControlSchemaMode = $this->getSchemaMode();
        $matchNullControlEntry = $this->doesSchemaModeProcessNullControlEntry();
        return \array_filter($entryList, function ($entry) use($typeResolverClass, $fieldInterfaceResolverClasses, $fieldName, $individualControlSchemaMode, $matchNullControlEntry) : bool {
            return ($entry[0] == $typeResolverClass || \in_array($entry[0], $fieldInterfaceResolverClasses)) && $entry[1] == $fieldName && (isset($entry[3]) && $entry[3] == $individualControlSchemaMode || !isset($entry[3]) && $matchNullControlEntry);
        });
    }
}
