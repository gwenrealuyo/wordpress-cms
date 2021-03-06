<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\EndpointExecuters;

use GraphQLAPI\GraphQLAPI\Constants\RequestParams;
use GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface;
use GraphQLAPI\GraphQLAPI\Services\CustomPostTypes\GraphQLEndpointCustomPostTypeInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;

abstract class AbstractEndpointExecuter implements EndpointExecuterInterface
{
    /**
     * @var \PoP\ComponentModel\Instances\InstanceManagerInterface
     */
    protected $instanceManager;
    /**
     * @var \GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface
     */
    protected $moduleRegistry;
    public function __construct(InstanceManagerInterface $instanceManager, ModuleRegistryInterface $moduleRegistry)
    {
        $this->instanceManager = $instanceManager;
        $this->moduleRegistry = $moduleRegistry;
    }
    public function getEnablingModule(): ?string
    {
        return null;
    }

    /**
     * Only enable the service, if the corresponding module is also enabled
     */
    public function isServiceEnabled(): bool
    {
        $enablingModule = $this->getEnablingModule();
        if ($enablingModule !== null && !$this->moduleRegistry->isModuleEnabled($enablingModule)) {
            return false;
        }

        // Check the expected ?view=... is requested
        if (!$this->isClientRequested()) {
            return false;
        }

        // Check we're loading the corresponding CPT
        $customPostType = $this->getCustomPostType();
        if (!\is_singular($customPostType->getCustomPostType())) {
            return false;
        }

        // Check the endpoint is not disabled
        global $post;
        if (!$customPostType->isEndpointEnabled($post)) {
            return false;
        }

        return true;
    }

    abstract protected function getCustomPostType(): GraphQLEndpointCustomPostTypeInterface;

    /**
     * Check the expected ?view=... is requested.
     */
    protected function isClientRequested(): bool
    {
        // Use `''` instead of `null` so that the query resolution
        // works either without param or empty (?view=)
        return ($_REQUEST[RequestParams::VIEW] ?? '') === $this->getView();
    }

    abstract protected function getView(): string;
}
