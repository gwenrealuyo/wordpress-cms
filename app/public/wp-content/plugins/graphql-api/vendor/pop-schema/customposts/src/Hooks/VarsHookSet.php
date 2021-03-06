<?php

declare (strict_types=1);
namespace PoPSchema\CustomPosts\Hooks;

use PoPSchema\CustomPosts\Constants\ModelInstanceComponentTypes;
use PoP\Hooks\AbstractHookSet;
use PoP\ComponentModel\ModelInstance\ModelInstance;
use PoPSchema\CustomPosts\Routing\RouteNatures;
use PoP\ComponentModel\State\ApplicationState;
use PoPSchema\CustomPosts\Facades\CustomPostTypeAPIFacade;
class VarsHookSet extends AbstractHookSet
{
    protected function init() : void
    {
        $this->hooksAPI->addFilter(ModelInstance::HOOK_COMPONENTS_RESULT, array($this, 'getModelInstanceComponentsFromVars'));
        $this->hooksAPI->addAction('augmentVarsProperties', [$this, 'augmentVarsProperties'], 10, 1);
    }
    public function getModelInstanceComponentsFromVars($components)
    {
        $vars = ApplicationState::getVars();
        $nature = $vars['nature'];
        // Properties specific to each nature
        switch ($nature) {
            case RouteNatures::CUSTOMPOST:
                // Single may depend on its post_type and category
                // Post and Event may be different
                // Announcements and Articles (Posts), or Past Event and (Upcoming) Event may be different
                // By default, we check for post type but not for categories
                $component_types = (array) $this->hooksAPI->applyFilters('\\PoP\\ComponentModel\\ModelInstanceProcessor_Utils:components_from_vars:type:single', array(ModelInstanceComponentTypes::SINGLE_CUSTOMPOST));
                if (\in_array(ModelInstanceComponentTypes::SINGLE_CUSTOMPOST, $component_types)) {
                    $customPostType = $vars['routing-state']['queried-object-post-type'];
                    $components[] = $this->translationAPI->__('post type:', 'pop-engine') . $customPostType;
                }
                break;
        }
        return $components;
    }
    /**
     * @param array<array> $vars_in_array
     */
    public function augmentVarsProperties(array $vars_in_array) : void
    {
        $vars =& $vars_in_array[0];
        $nature = $vars['nature'];
        $vars['routing-state']['is-custompost'] = $nature == RouteNatures::CUSTOMPOST;
        // Attributes needed to match the RouteModuleProcessor vars conditions
        if ($nature == RouteNatures::CUSTOMPOST) {
            $customPostTypeAPI = CustomPostTypeAPIFacade::getInstance();
            $customPostID = $vars['routing-state']['queried-object-id'];
            $vars['routing-state']['queried-object-post-type'] = $customPostTypeAPI->getCustomPostType($customPostID);
        }
    }
}
