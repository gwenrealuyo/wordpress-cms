<?php

declare (strict_types=1);
namespace PoP\ComponentModel\AttachableExtensions;

use PoP\ComponentModel\AttachableExtensions\AttachableExtensionInterface;
class AttachableExtensionManager implements \PoP\ComponentModel\AttachableExtensions\AttachableExtensionManagerInterface
{
    /**
     * @var array<string, array<string, AttachableExtensionInterface[]>>
     */
    protected $attachableExtensions = [];
    public function attachExtensionToClass(string $attachableClass, string $group, AttachableExtensionInterface $attachableExtension) : void
    {
        $this->attachableExtensions[$attachableClass][$group][] = $attachableExtension;
    }
    /**
     * @return AttachableExtensionInterface[]
     */
    public function getAttachedExtensions(string $attachableClass, string $group) : array
    {
        return $this->attachableExtensions[$attachableClass][$group] ?? [];
    }
}
