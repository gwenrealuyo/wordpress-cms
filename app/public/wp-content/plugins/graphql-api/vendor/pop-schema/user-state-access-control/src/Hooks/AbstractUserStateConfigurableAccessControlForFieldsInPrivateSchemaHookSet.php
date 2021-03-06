<?php

declare (strict_types=1);
namespace PoPSchema\UserStateAccessControl\Hooks;

use PoP\ComponentModel\State\ApplicationState;
use PoPSchema\UserStateAccessControl\Services\AccessControlGroups;
use PoP\AccessControl\Hooks\AbstractConfigurableAccessControlForFieldsInPrivateSchemaHookSet;
abstract class AbstractUserStateConfigurableAccessControlForFieldsInPrivateSchemaHookSet extends AbstractConfigurableAccessControlForFieldsInPrivateSchemaHookSet
{
    /**
     * Configuration entries
     */
    protected function getConfigurationEntries() : array
    {
        return $this->accessControlManager->getEntriesForFields(AccessControlGroups::STATE);
    }
    protected function removeFieldNameBasedOnMatchingEntryValue($entryValue = null) : bool
    {
        // Obtain the user state: logged in or not
        $vars = ApplicationState::getVars();
        $isUserLoggedIn = $vars['global-userstate']['is-user-logged-in'];
        // Let the implementation class decide if to remove the field or not
        return $this->removeFieldNameBasedOnUserState((string) $entryValue, $isUserLoggedIn);
    }
    protected abstract function removeFieldNameBasedOnUserState(string $entryValue, bool $isUserLoggedIn) : bool;
}
