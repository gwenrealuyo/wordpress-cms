<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\Services\MenuPages;

use GraphQLAPI\GraphQLAPI\Constants\RequestParams;
use GraphQLAPI\GraphQLAPI\ContentProcessors\PluginMarkdownContentRetrieverTrait;
use GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface;
use GraphQLAPI\GraphQLAPI\Services\Helpers\EndpointHelpers;
use GraphQLAPI\GraphQLAPI\Services\Helpers\MenuPageHelper;
use GraphQLAPI\GraphQLAPI\Services\MenuPages\AbstractDocsMenuPage;
use GraphQLAPI\GraphQLAPI\Services\MenuPages\ModulesMenuPage;
use InvalidArgumentException;
use PoP\ComponentModel\Facades\Instances\InstanceManagerFacade;
use PoP\ComponentModel\Instances\InstanceManagerInterface;

/**
 * Module Documentation menu page
 */
class ModuleDocumentationMenuPage extends AbstractDocsMenuPage
{
    use PluginMarkdownContentRetrieverTrait;
    /**
     * @var \GraphQLAPI\GraphQLAPI\Registries\ModuleRegistryInterface
     */
    protected $moduleRegistry;

    public function __construct(
        InstanceManagerInterface $instanceManager,
        MenuPageHelper $menuPageHelper,
        EndpointHelpers $endpointHelpers,
        ModuleRegistryInterface $moduleRegistry
    ) {
        $this->moduleRegistry = $moduleRegistry;
        parent::__construct(
            $instanceManager,
            $menuPageHelper,
            $endpointHelpers
        );
    }

    public function getMenuPageSlug(): string
    {
        $instanceManager = InstanceManagerFacade::getInstance();
        /**
         * @var ModulesMenuPage
         */
        $modulesMenuPage = $instanceManager->getInstance(ModulesMenuPage::class);
        return $modulesMenuPage->getMenuPageSlug();
    }

    /**
     * Validate the param also
     */
    protected function isCurrentScreen(): bool
    {
        return $this->menuPageHelper->isDocumentationScreen() && parent::isCurrentScreen();
    }

    protected function openInModalWindow(): bool
    {
        return true;
    }

    protected function useTabpanelForContent(): bool
    {
        return true;
    }

    protected function getContentToPrint(): string
    {
        // This is crazy: passing ?module=Foo\Bar\module,
        // and then doing $_GET['module'], returns "Foo\\Bar\\module"
        // So parse the URL to extract the "module" param
        $vars = [];
        parse_str($_SERVER['REQUEST_URI'], $vars);
        $module = urldecode($vars[RequestParams::MODULE]);
        try {
            $moduleResolver = $this->moduleRegistry->getModuleResolver($module);
        } catch (InvalidArgumentException $exception) {
            return sprintf(
                '<p>%s</p>',
                sprintf(
                    \__('Oops, module \'%s\' does not exist', 'graphql-api'),
                    $module
                )
            );
        }
        $hasDocumentation = $moduleResolver->hasDocumentation($module);
        $documentation = '';
        if ($hasDocumentation) {
            $documentation = $moduleResolver->getDocumentation($module);
        }
        if (!$hasDocumentation || $documentation === null) {
            return sprintf(
                '<p>%s</p>',
                sprintf(
                    \__('Oops, module \'%s\' has no documentation', 'graphql-api'),
                    $moduleResolver->getName($module)
                )
            );
        }
        return $documentation;
    }
}
