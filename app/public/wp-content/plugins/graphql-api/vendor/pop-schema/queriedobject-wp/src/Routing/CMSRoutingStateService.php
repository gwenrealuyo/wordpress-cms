<?php

declare(strict_types=1);

namespace PoPSchema\QueriedObjectWP\Routing;

use WP_Query;
use PoP\RoutingWP\RoutingManagerTrait;
use PoPSchema\QueriedObject\Routing\CMSRoutingStateServiceInterface;

class CMSRoutingStateService implements CMSRoutingStateServiceInterface
{
    use RoutingManagerTrait;

    /**
     * @return object|null
     */
    public function getQueriedObject()
    {
        $this->init();
        /** @var WP_Query */
        $query = $this->query;
        if ($this->isStandard()) {
            return null;
        } elseif (
            $query->is_tag() ||
            $query->is_page() ||
            $query->is_single() ||
            $query->is_author() ||
            $query->is_category()
        ) {
            return $query->get_queried_object();
        }

        return null;
    }

    /**
     * @return string|int|null
     */
    public function getQueriedObjectId()
    {
        $this->init();
        if ($this->isStandard()) {
            return null;
        } elseif (
            $this->query->is_tag() ||
            $this->query->is_page() ||
            $this->query->is_single() ||
            $this->query->is_author() ||
            $this->query->is_category()
        ) {
            return $this->query->get_queried_object_id();
        }

        return null;
    }
}
