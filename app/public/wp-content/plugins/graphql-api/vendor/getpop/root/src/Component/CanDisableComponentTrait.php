<?php

declare (strict_types=1);
namespace PoP\Root\Component;

trait CanDisableComponentTrait
{
    /**
     * @var bool|null
     */
    protected static $enabled;
    protected static function resolveEnabled() : bool
    {
        return \true;
    }
    public static function isEnabled()
    {
        // This is needed for if asking if this component is enabled before it has been initialized
        if (\is_null(self::$enabled)) {
            self::$enabled = self::resolveEnabled();
        }
        return self::$enabled;
    }
}
