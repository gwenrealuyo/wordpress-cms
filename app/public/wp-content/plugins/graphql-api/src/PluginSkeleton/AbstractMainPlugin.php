<?php

declare(strict_types=1);

namespace GraphQLAPI\GraphQLAPI\PluginSkeleton;

use Exception;
use GraphQLAPI\ExternalDependencyWrappers\Symfony\Component\Filesystem\FilesystemWrapper;
use GraphQLAPI\GraphQLAPI\Facades\UserSettingsManagerFacade;
use GraphQLAPI\GraphQLAPI\PluginEnvironment;
use GraphQLAPI\GraphQLAPI\PluginManagement\ExtensionManager;
use GraphQLAPI\GraphQLAPI\PluginManagement\MainPluginManager;
use GraphQLAPI\GraphQLAPI\PluginSkeleton\AbstractPlugin;
use PoP\Engine\AppLoader;
use PoP\Root\Environment as RootEnvironment;
use RuntimeException;

abstract class AbstractMainPlugin extends AbstractPlugin
{
    /**
     * If there is any error when initializing the plugin,
     * set this var to `true` to stop loading it and show an error message.
     * @var \Exception|null
     */
    protected $inititalizationException;
    /**
     * @var \GraphQLAPI\GraphQLAPI\PluginSkeleton\AbstractMainPluginConfiguration
     */
    protected $pluginConfiguration;
    public function __construct(
        string $pluginFile,
        /** The main plugin file */
        string $pluginVersion,
        ?string $pluginName = null,
        AbstractMainPluginConfiguration $pluginConfiguration
    )
    {
        $this->pluginConfiguration = $pluginConfiguration;
        parent::__construct($pluginFile, $pluginVersion, $pluginName);
    }

    /**
     * Configure the plugin.
     * This defines hooks to set environment variables,
     * so must be executed before those hooks are triggered for first time
     * (in ComponentConfiguration classes)
     */
    protected function callPluginConfiguration(): void
    {
        $this->pluginConfiguration->initialize();
    }

    /**
     * Add configuration for the Component classes
     *
     * @return array<string, mixed> [key]: Component class, [value]: Configuration
     */
    public function getComponentClassConfiguration(): array
    {
        return $this->pluginConfiguration->getComponentClassConfiguration();
    }

    /**
     * Add schema Component classes to skip initializing
     *
     * @return string[] List of `Component` class which must not initialize their Schema services
     */
    public function getSchemaComponentClassesToSkip(): array
    {
        return $this->pluginConfiguration->getSchemaComponentClassesToSkip();
    }

    /**
     * Get the plugin's immutable configuration values
     *
     * @return array<string, mixed>
     */
    protected function doGetFullConfiguration(): array
    {
        return array_merge(
            parent::doGetFullConfiguration(),
            [
                /**
                 * Where to store the config cache,
                 * for both /service-containers and /config-via-symfony-cache
                 * (config persistent cache: component model configuration + schema)
                 */
                'cache-dir' => PluginEnvironment::getCacheDir(),
            ]
        );
    }

    /**
     * When activating/deactivating ANY plugin (either from GraphQL API
     * or 3rd-parties), the cached service container and the config
     * must be dumped, so that they can be regenerated.
     *
     * This way, extensions depending on 3rd-party plugins
     * can have their functionality automatically enabled/disabled.
     */
    public function handleAnyPluginActivatedOrDeactivated(): void
    {
        $this->invalidateCache();
    }


    /**
     * Remove the cached folders (service container and config),
     * and regenerate the timestamp
     */
    protected function invalidateCache(): void
    {
        $this->removeCachedFolders();

        // Regenerate the timestamp
        $userSettingsManager = UserSettingsManagerFacade::getInstance();
        $userSettingsManager->storeTimestamp();
    }

    /**
     * Remove the cached folders
     */
    protected function removeCachedFolders(): void
    {
        $fileSystemWrapper = new FilesystemWrapper();
        try {
            $fileSystemWrapper->remove((string) MainPluginManager::getConfig('cache-dir'));
        } catch (RuntimeException $exception) {
            // If the folder does not exist, do nothing
        }
    }

    /**
     * Remove permalinks when deactivating the plugin
     *
     * @see https://developer.wordpress.org/plugins/plugin-basics/activation-deactivation-hooks/
     */
    public function deactivate(): void
    {
        parent::deactivate();

        // Remove the timestamp
        $this->removeTimestamp();
    }

    /**
     * Regenerate the timestamp
     */
    protected function removeTimestamp(): void
    {
        $userSettingsManager = UserSettingsManagerFacade::getInstance();
        $userSettingsManager->removeTimestamp();
    }

    /**
     * There are three stages for the main plugin, and for each extension plugin:
     * `setup`, `initialize` and `boot`.
     *
     * This is because:
     *
     * - The plugin must execute its logic before the extensions
     * - The services can't be booted before all services have been initialized
     *
     * To attain the needed order, we execute them using hook "plugins_loaded",
     * with all the priorities defined in PluginLifecyclePriorities
     */
    public function setup(): void
    {
        parent::setup();

        /**
         * When activating/deactivating ANY plugin (either from GraphQL API
         * or 3rd-parties), the cached service container and the config
         * must be dumped, so that they can be regenerated.
         *
         * This way, extensions depending on 3rd-party plugins
         * can have their functionality automatically enabled/disabled.
         */
        \add_action('activate_plugin', [$this, 'handleAnyPluginActivatedOrDeactivated']);
        \add_action('deactivate_plugin', [$this, 'handleAnyPluginActivatedOrDeactivated']);

        /**
         * PoP depends on hook "init" to set-up the endpoint rewrite,
         * as in function `addRewriteEndpoints` in `AbstractEndpointHandler`
         * However, activating the plugin takes place AFTER hooks "plugins_loaded"
         * and "init". Hence, the code cannot flush the rewrite_rules when the plugin
         * is activated, and any non-default GraphQL endpoint is not set.
         *
         * The solution (hack) is to check if the plugin has just been installed,
         * and then apply the logic, on every request in the admin!
         *
         * @see https://developer.wordpress.org/reference/functions/register_activation_hook/#process-flow
         */
        \register_activation_hook($this->getPluginFile(), [$this, 'activate']);

        // Dump the container whenever a new plugin or extension is activated
        $this->handleNewActivations();

        // Initialize the procedure to register/initialize plugin and extensions
        $this->executeSetupProcedure();
    }

    /**
     * Check if the plugin has just been activated or updated,
     * or if an extension has just been activated.
     *
     * Regenerate the container here, and not in the `activate` function,
     * because `activate` doesn't get called within the "plugins_loaded" hook.
     * This is not an issue to register the main plugin, but it is for extensions,
     * since they need to ask if the main plugin exists (since AbstractExtension
     * is located there), and that can only be done in "plugins_loaded".
     */
    protected function handleNewActivations(): void
    {
        /**
         * Logic to check if the main plugin or any extension has just been activated or updated.
         */
        \add_action(
            'plugins_loaded',
            function (): void {
                if (!\is_admin() || $this->inititalizationException !== null) {
                    return;
                }
                $storedPluginVersions = \get_option(PluginOptions::PLUGIN_VERSIONS, []);
                $registeredExtensionBaseNameInstances = ExtensionManager::getExtensions();

                // Check if the main plugin has been activated or updated
                $isMainPluginJustActivated = !isset($storedPluginVersions[$this->pluginBaseName]);
                $isMainPluginJustUpdated = !$isMainPluginJustActivated && $storedPluginVersions[$this->pluginBaseName] !== $this->pluginVersion;

                // Check if any extension has been activated or updated
                $justActivatedExtensions = [];
                $justUpdatedExtensions = [];
                foreach ($registeredExtensionBaseNameInstances as $extensionBaseName => $extensionInstance) {
                    if (!isset($storedPluginVersions[$extensionBaseName])) {
                        $justActivatedExtensions[$extensionBaseName] = $extensionInstance;
                    } elseif ($storedPluginVersions[$extensionBaseName] !== $extensionInstance->getPluginVersion()) {
                        $justUpdatedExtensions[$extensionBaseName] = $extensionInstance;
                    }
                }

                // Check if any extension has been deactivated
                $justDeactivatedExtensionBaseNames = array_diff(
                    array_keys($storedPluginVersions),
                    [
                        $this->pluginBaseName,
                    ],
                    array_keys($registeredExtensionBaseNameInstances)
                );

                // If there were no changes, nothing to do
                if (
                    !$isMainPluginJustActivated
                    && !$isMainPluginJustUpdated
                    && $justActivatedExtensions === []
                    && $justUpdatedExtensions === []
                    && $justDeactivatedExtensionBaseNames === []
                ) {
                    return;
                }

                // Enable to implement custom additional functionality (eg: show admin notice with changelog)
                // Watch out! Execute at the very end, just in case they need to access the service container,
                // which is not initialized yet (eg: for calling `$userSettingsManager->getSetting`)
                \add_action(
                    'plugins_loaded',
                    function () use ($isMainPluginJustActivated, $isMainPluginJustUpdated, $storedPluginVersions, $justActivatedExtensions, $justUpdatedExtensions) : void {
                        if ($isMainPluginJustActivated) {
                            $this->pluginJustActivated();
                        } elseif ($isMainPluginJustUpdated) {
                            $this->pluginJustUpdated($storedPluginVersions[$this->pluginBaseName]);
                        }
                        foreach ($justActivatedExtensions as $extensionBaseName => $extensionInstance) {
                            $extensionInstance->pluginJustActivated();
                        }
                        foreach ($justUpdatedExtensions as $extensionBaseName => $extensionInstance) {
                            $extensionInstance->pluginJustUpdated($storedPluginVersions[$extensionBaseName]);
                        }
                    },
                    PluginLifecyclePriorities::AFTER_EVERYTHING
                );

                // Recalculate the updated entry and update on the DB
                $storedPluginVersions[$this->pluginBaseName] = $this->pluginVersion;
                foreach (array_merge($justActivatedExtensions, $justUpdatedExtensions) as $extensionBaseName => $extensionInstance) {
                    $storedPluginVersions[$extensionBaseName] = $extensionInstance->getPluginVersion();
                }
                foreach ($justDeactivatedExtensionBaseNames as $extensionBaseName) {
                    unset($storedPluginVersions[$extensionBaseName]);
                }
                \update_option(PluginOptions::PLUGIN_VERSIONS, $storedPluginVersions);

                // If new CPTs have rewrite rules, these must be flushed
                \flush_rewrite_rules();

                // Regenerate the timestamp, to generate the service container
                $this->invalidateCache();
            },
            PluginLifecyclePriorities::HANDLE_NEW_ACTIVATIONS
        );
    }

    /**
     * There are three stages for the main plugin, and for each extension plugin:
     * `setup`, `initialize` and `boot`.
     *
     * This is because:
     *
     * - The plugin must execute its logic before the extensions
     * - The services can't be booted before all services have been initialized
     *
     * To attain the needed order, we execute them using hook "plugins_loaded",
     * with all the priorities defined in PluginLifecyclePriorities
     */
    final protected function executeSetupProcedure(): void
    {
        /**
         * Wait until "plugins_loaded" to initialize the plugin, because:
         *
         * - ModuleListTableAction requires `wp_verify_nonce`, loaded in pluggable.php
         * - Allow other plugins to inject their own functionality
         */
        \add_action(
            'plugins_loaded',
            function () {
                if ($this->inititalizationException !== null) {
                    return;
                }
                $this->initialize();
            },
            PluginLifecyclePriorities::INITIALIZE_PLUGIN
        );
        \add_action(
            'plugins_loaded',
            function () {
                if ($this->inititalizationException !== null) {
                    return;
                }
                \do_action(PluginLifecycleHooks::INITIALIZE_EXTENSION);
            },
            PluginLifecyclePriorities::INITIALIZE_EXTENSIONS
        );
        \add_action(
            'plugins_loaded',
            function () {
                if ($this->inititalizationException !== null) {
                    return;
                }
                $this->bootSystem();
            },
            PluginLifecyclePriorities::BOOT_SYSTEM
        );
        \add_action(
            'plugins_loaded',
            function () {
                if ($this->inititalizationException !== null) {
                    return;
                }
                $this->configure();
            },
            PluginLifecyclePriorities::CONFIGURE_PLUGIN
        );
        \add_action(
            'plugins_loaded',
            function () {
                if ($this->inititalizationException !== null) {
                    return;
                }
                \do_action(PluginLifecycleHooks::CONFIGURE_EXTENSION);
            },
            PluginLifecyclePriorities::CONFIGURE_EXTENSIONS
        );
        \add_action(
            'plugins_loaded',
            function () {
                if ($this->inititalizationException !== null) {
                    return;
                }
                $this->bootApplication();
            },
            PluginLifecyclePriorities::BOOT_APPLICATION
        );
        \add_action(
            'plugins_loaded',
            function () {
                if ($this->inititalizationException !== null) {
                    return;
                }
                $this->boot();
            },
            PluginLifecyclePriorities::BOOT_PLUGIN
        );
        \add_action(
            'plugins_loaded',
            function () {
                if ($this->inititalizationException !== null) {
                    return;
                }
                \do_action(PluginLifecycleHooks::BOOT_EXTENSION);
            },
            PluginLifecyclePriorities::BOOT_EXTENSIONS
        );
        \add_action(
            'plugins_loaded',
            function () {
                $this->handleInitializationException();
            },
            PHP_INT_MAX
        );
    }

    /**
     * Boot the system
     */
    public function bootSystem(): void
    {
        // If the service container has an error, Symfony DI will throw an exception
        try {
            // Boot all PoP components, from this plugin and all extensions
            $containerCacheConfiguration = $this->pluginConfiguration->getContainerCacheConfiguration();
            AppLoader::bootSystem($containerCacheConfiguration->cacheContainerConfiguration(), $containerCacheConfiguration->getContainerConfigurationCacheNamespace(), $containerCacheConfiguration->getContainerConfigurationCacheDirectory());

            // Custom logic
            $this->doBootSystem();
        } catch (Exception $e) {
            $this->inititalizationException = $e;
        }
    }

    /**
     * Custom function to boot the system. Override if needed
     */
    protected function doBootSystem(): void
    {
    }

    /**
     * Boot the application
     */
    public function bootApplication(): void
    {
        // If the service container has an error, Symfony DI will throw an exception
        try {
            // Boot all PoP components, from this plugin and all extensions
            $containerCacheConfiguration = $this->pluginConfiguration->getContainerCacheConfiguration();
            AppLoader::bootApplication($containerCacheConfiguration->cacheContainerConfiguration(), $containerCacheConfiguration->getContainerConfigurationCacheNamespace(), $containerCacheConfiguration->getContainerConfigurationCacheDirectory());

            // Custom logic
            $this->doBootApplication();
        } catch (Exception $e) {
            $this->inititalizationException = $e;
        }
    }

    /**
     * Custom function to boot the application. Override if needed
     */
    protected function doBootApplication(): void
    {
    }

    /**
     * If in development, throw the exception.
     * If in production, show the error as an admin notice.
     */
    protected function handleInitializationException(): void
    {
        if ($this->inititalizationException !== null) {
            if (RootEnvironment::isApplicationEnvironmentDev()) {
                throw $this->inititalizationException;
            } else {
                \add_action('admin_notices', function () {
                    // Avoid PHPStan error
                    /** @var Exception */
                    $inititalizationException = $this->inititalizationException;
                    $errorMessage = \__('<p><em>(This message is visible only by the admin.)</em></p>', 'graphql-api')
                    . sprintf(
                        \__('<p>Something went wrong initializing plugin <strong>%s</strong> (so it has not been loaded):</p><code>%s</code><p>Stack trace:</p><pre>%s</pre>', 'graphql-api'),
                        $this->pluginName,
                        $inititalizationException->getMessage(),
                        $inititalizationException->getTraceAsString()
                    );
                    _e(sprintf(
                        '<div class="notice notice-error">%s</div>',
                        $errorMessage
                    ));
                });
            }
        }
    }
}
