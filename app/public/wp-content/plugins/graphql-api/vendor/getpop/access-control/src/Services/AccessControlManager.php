<?php

declare (strict_types=1);
namespace PoP\AccessControl\Services;

use PoP\AccessControl\Services\AccessControlManagerInterface;
class AccessControlManager implements AccessControlManagerInterface
{
    /**
     * @var array<string, array>
     */
    protected $fieldEntries = [];
    /**
     * @var array<string, array>
     */
    protected $directiveEntries = [];
    public function getEntriesForFields(string $group) : array
    {
        return $this->fieldEntries[$group] ?? [];
    }
    public function getEntriesForDirectives(string $group) : array
    {
        return $this->directiveEntries[$group] ?? [];
    }
    public function addEntriesForFields(string $group, array $fieldEntries) : void
    {
        $this->fieldEntries[$group] = \array_merge($this->fieldEntries[$group] ?? [], $fieldEntries);
    }
    public function addEntriesForDirectives(string $group, array $directiveEntries) : void
    {
        $this->directiveEntries[$group] = \array_merge($this->directiveEntries[$group] ?? [], $directiveEntries);
    }
}
