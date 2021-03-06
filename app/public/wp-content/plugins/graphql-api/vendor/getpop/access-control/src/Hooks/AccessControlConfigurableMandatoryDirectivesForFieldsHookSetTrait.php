<?php

declare (strict_types=1);
namespace PoP\AccessControl\Hooks;

use PoP\AccessControl\ComponentConfiguration;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\ComponentModel\FieldResolvers\FieldResolverInterface;
trait AccessControlConfigurableMandatoryDirectivesForFieldsHookSetTrait
{
    public function maybeFilterFieldName(bool $include, TypeResolverInterface $typeResolver, FieldResolverInterface $fieldResolver, array $fieldInterfaceResolverClasses, string $fieldName) : bool
    {
        /**
         * If enabling individual control, then check if there is any entry for this field and schema mode
         */
        if (ComponentConfiguration::enableIndividualControlForPublicPrivateSchemaMode()) {
            /**
             * If there are no entries, then exit by returning the original hook value
             */
            if (empty($this->getEntries($typeResolver, $fieldInterfaceResolverClasses, $fieldName))) {
                return $include;
            }
        }
        /**
         * The parent case deals with the general case
         */
        return parent::maybeFilterFieldName($include, $typeResolver, $fieldResolver, $fieldInterfaceResolverClasses, $fieldName);
    }
}
