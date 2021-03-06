<?php

declare (strict_types=1);
namespace PoP\ComponentModel\HelperServices;

use PrefixedByPoP\Composer\Semver\Semver;
class SemverHelperService implements \PoP\ComponentModel\HelperServices\SemverHelperServiceInterface
{
    /**
     * Determine if given version satisfies given constraints.
     */
    public function satisfies(string $version, string $constraints) : bool
    {
        return Semver::satisfies($version, $constraints);
    }
}
