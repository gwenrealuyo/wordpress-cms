<?php

declare (strict_types=1);
namespace PoP\ComponentModel\Container\CompilerPasses;

use PoP\ComponentModel\AttachableExtensions\AttachableExtensionGroups;
use PoP\Root\Component\ApplicationEvents;
use PoP\ComponentModel\DirectiveResolvers\DirectiveResolverInterface;
use PoP\ComponentModel\FieldResolvers\FieldResolverInterface;
use PoP\ComponentModel\TypeResolverPickers\TypeResolverPickerInterface;
class BootAttachExtensionCompilerPass extends \PoP\ComponentModel\Container\CompilerPasses\AbstractAttachExtensionCompilerPass
{
    protected function getAttachExtensionEvent() : string
    {
        return ApplicationEvents::BOOT;
    }
    /**
     * @return array<string,string>
     */
    protected function getAttachableClassGroups() : array
    {
        return [FieldResolverInterface::class => AttachableExtensionGroups::FIELDRESOLVERS, DirectiveResolverInterface::class => AttachableExtensionGroups::DIRECTIVERESOLVERS, TypeResolverPickerInterface::class => AttachableExtensionGroups::TYPERESOLVERPICKERS];
    }
}
