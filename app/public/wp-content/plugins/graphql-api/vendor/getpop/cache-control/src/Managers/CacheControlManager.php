<?php

declare (strict_types=1);
namespace PoP\CacheControl\Managers;

use PoP\CacheControl\Managers\CacheControlManagerInterface;
class CacheControlManager implements CacheControlManagerInterface
{
    /**
     * @var array[]
     */
    protected $fieldEntries = [];
    /**
     * @var array[]
     */
    protected $directiveEntries = [];
    public function getEntriesForFields() : array
    {
        return $this->fieldEntries ?? [];
    }
    public function getEntriesForDirectives() : array
    {
        return $this->directiveEntries ?? [];
    }
    public function addEntriesForFields(array $fieldEntries) : void
    {
        $this->fieldEntries = \array_merge($this->fieldEntries ?? [], $fieldEntries);
    }
    public function addEntriesForDirectives(array $directiveEntries) : void
    {
        $this->directiveEntries = \array_merge($this->directiveEntries ?? [], $directiveEntries);
    }
}
