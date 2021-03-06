<?php

declare(strict_types=1);

namespace PoP\EngineWP\CMS;

use PoP\Engine\CMS\CMSServiceInterface;

use function get_option;
use function home_url;
use function get_site_url;

class CMSService implements CMSServiceInterface
{
    /**
     * @param mixed $default
     * @return mixed
     */
    public function getOption(string $option, $default = false)
    {
        return get_option($option, $default);
    }

    public function getHomeURL(string $path = ''): string
    {
        return home_url($path);
    }

    public function getSiteURL(): string
    {
        return get_site_url();
    }
}
