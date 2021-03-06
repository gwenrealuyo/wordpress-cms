<?php

declare (strict_types=1);
namespace PoP\ComponentModel\Enums;

use Exception;
use PoP\ComponentModel\Schema\SchemaHelpers;
use PoP\ComponentModel\State\ApplicationState;
abstract class AbstractEnum implements \PoP\ComponentModel\Enums\EnumInterface
{
    /**
     * Validate that, if the enum provides core values,
     * these have the same number of elements as the values
     */
    public function __construct()
    {
        if (!\is_null($this->getCoreValues()) && \count($this->getCoreValues()) != \count($this->getValues())) {
            throw new Exception(\sprintf('Enum \'%s\' (in class \'%s\') must return the same number of elements in function `getCoreValues()` as in `getValues()`', $this->getName(), \get_called_class()));
        }
    }
    public final function getName() : string
    {
        return $this->getMaybeNamespacedName();
    }
    public function getNamespace() : string
    {
        return SchemaHelpers::getSchemaNamespace(\get_called_class());
    }
    public final function getNamespacedName() : string
    {
        return SchemaHelpers::getSchemaNamespacedName($this->getNamespace(), $this->getEnumName());
    }
    public final function getMaybeNamespacedName() : string
    {
        $vars = ApplicationState::getVars();
        return $vars['namespace-types-and-interfaces'] ? $this->getNamespacedName() : $this->getEnumName();
    }
    /**
     * Enum name
     */
    protected abstract function getEnumName() : string;
    /**
     * Allow the enum to deal with 2 values: the one exposed on the API,
     * and the real one that needs be provided to the application.
     *
     * To pair them, they must be on the same position in their respective arrays:
     * values => ["ONE", "TWO"] and coreValues => ["one", "two"]
     *
     * If `null`, `getValues` is used
     */
    public function getCoreValues() : ?array
    {
        return null;
    }
    /**
     * Given an enum value, obtain its core value
     */
    public final function getCoreValue(string $enumValue) : ?string
    {
        // If no core values defined, then search for it in values
        $values = $this->getValues();
        $coreValues = $this->getCoreValues();
        if (!$coreValues) {
            $coreValues = $values;
        }
        // Get the index for the enum in the values
        $pos = \array_search($enumValue, $values);
        if ($pos === \false) {
            return null;
        }
        // The core value and the value are at the same position in the array
        return $coreValues[$pos];
    }
}
