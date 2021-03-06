<?php

declare(strict_types=1);

namespace PoP\RoutingWP;

use WP_Query;

trait RoutingManagerTrait
{
    /**
     * @var \WP_Query|null
     */
    private $query;

    private function init(): void
    {
        if (is_null($this->query)) {
            global $wp_query;
            $this->query = $wp_query;
        }
    }

    private function isStandard(): bool
    {
        /** @var WP_Query */
        $query = $this->query;
        // If we passed query args STANDARD_NATURE, then it's a route
        // Compare the keys only, because since PHP 8.0, comparing array values
        // (included in $query->query_vars) throws error
        return !empty(
            array_intersect(
                array_keys($query->query_vars),
                array_keys(WPQueries::STANDARD_NATURE)
            )
        );
    }
}
