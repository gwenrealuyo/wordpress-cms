<?php

declare(strict_types=1);

namespace PoPSchema\CategoriesWP\TypeAPIs;

use PoP\ComponentModel\TypeDataResolvers\InjectedFilterDataloadingModuleTypeDataResolverTrait;
use PoP\Engine\Facades\CMS\CMSServiceFacade;
use PoP\Hooks\HooksAPIInterface;
use PoPSchema\Categories\ComponentConfiguration;
use PoPSchema\Categories\TypeAPIs\CategoryTypeAPIInterface;
use PoPSchema\QueriedObject\Helpers\QueriedObjectHelperServiceInterface;
use PoPSchema\SchemaCommons\DataLoading\ReturnTypes;
use PoPSchema\TaxonomiesWP\TypeAPIs\TaxonomyTypeAPI;
use WP_Taxonomy;

/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
abstract class AbstractCategoryTypeAPI extends TaxonomyTypeAPI implements CategoryTypeAPIInterface
{
    use InjectedFilterDataloadingModuleTypeDataResolverTrait;
    /**
     * @var \PoP\Hooks\HooksAPIInterface
     */
    protected $hooksAPI;
    /**
     * @var \PoPSchema\QueriedObject\Helpers\QueriedObjectHelperServiceInterface
     */
    protected $queriedObjectHelperService;
    public function __construct(HooksAPIInterface $hooksAPI, QueriedObjectHelperServiceInterface $queriedObjectHelperService)
    {
        $this->hooksAPI = $hooksAPI;
        $this->queriedObjectHelperService = $queriedObjectHelperService;
    }

    /**
     * Indicates if the passed object is of type Category
     * @param object $object
     */
    public function isInstanceOfCategoryType($object): bool
    {
        return ($object instanceof WP_Taxonomy) && $object->hierarchical == true;
    }

    /**
     * @return string|int
     * @param object $cat
     */
    public function getCategoryID($cat)
    {
        return $cat->term_id;
    }

    abstract protected function getCategoryBaseOption(): string;

    abstract protected function getCategoryTaxonomyName(): string;

    public function getCategory($category_id)
    {
        return get_category($category_id, $this->getCategoryTaxonomyName());
    }
    public function getCategoryByName($category_name)
    {
        return get_term_by('name', $category_name, $this->getCategoryTaxonomyName());
    }
    /**
     * @param string|int $customPostID
     */
    public function getCustomPostCategories($customPostID, array $query = [], array $options = []): array
    {
        $query = $this->convertCategoriesQuery($query, $options);

        return \wp_get_post_terms($customPostID, $this->getCategoryTaxonomyName(), $query);
    }
    /**
     * @param string|int $customPostID
     */
    public function getCustomPostCategoryCount($customPostID, array $query = [], array $options = []): int
    {
        // There is no direct way to calculate the total
        // (Documentation mentions to pass arg "count" => `true` to `wp_get_post_categories`,
        // but it doesn't work)
        // So execute a normal `wp_get_post_categories` retrieving all the IDs, and count them
        $options['return-type'] = ReturnTypes::IDS;
        $query = $this->convertCategoriesQuery($query, $options);

        // All results, no offset
        $query['number'] = 0;
        unset($query['offset']);

        // Resolve and count
        $categories = \wp_get_post_terms($customPostID, $this->getCategoryTaxonomyName(), $query);
        return count($categories);
    }
    public function getCategoryCount(array $query = [], array $options = []): int
    {
        // There is no direct way to calculate the total
        // (Documentation mentions to pass arg "count" => `true` to `get_categories`,
        // but it doesn't work)
        // So execute a normal `get_categories` retrieving all the IDs, and count them
        $options['return-type'] = ReturnTypes::IDS;
        $query = $this->convertCategoriesQuery($query, $options);

        // All results, no offset
        $query['number'] = 0;
        unset($query['offset']);

        // Resolve and count
        $categories = get_categories($query);
        return count($categories);
    }
    public function getCategories(array $query, array $options = []): array
    {
        $query = $this->convertCategoriesQuery($query, $options);
        return get_categories($query);
    }

    public function convertCategoriesQuery(array $query, array $options = []): array
    {
        $query['taxonomy'] = $this->getCategoryTaxonomyName();

        if ($return_type = $options['return-type'] ?? null) {
            if ($return_type == ReturnTypes::IDS) {
                $query['fields'] = 'ids';
            } elseif ($return_type == ReturnTypes::NAMES) {
                $query['fields'] = 'names';
            }
        }

        // Accept field atts to filter the API fields
        $this->maybeFilterDataloadQueryArgs($query, $options);

        if (isset($query['hide-empty'])) {
            $query['hide_empty'] = $query['hide-empty'];
            unset($query['hide-empty']);
        } else {
            // By default: do not hide empty categories
            $query['hide_empty'] = false;
        }

        // Convert the parameters
        if (isset($query['include'])) {
            // Transform from array to string
            $query['include'] = implode(',', $query['include']);
        }
        if (isset($query['order'])) {
            // Same param name, so do nothing
        }
        if (isset($query['orderby'])) {
            // Same param name, so do nothing
            // This param can either be a string or an array. Eg:
            // $query['orderby'] => array('date' => 'DESC', 'title' => 'ASC');
        }
        if (isset($query['offset'])) {
            // Same param name, so do nothing
        }
        if (isset($query['limit'])) {
            // Maybe restrict the limit, if higher than the max limit
            // Allow to not limit by max when querying from within the application
            $limit = (int) $query['limit'];
            if (!isset($options['skip-max-limit']) || !$options['skip-max-limit']) {
                $limit = $this->queriedObjectHelperService->getLimitOrMaxLimit(
                    $limit,
                    ComponentConfiguration::getCategoryListMaxLimit()
                );
            }

            // Assign the limit as the required attribute
            // To bring all results, get_categories needs "number => 0" instead of -1
            $query['number'] = ($limit == -1) ? 0 : $limit;
            unset($query['limit']);
        }
        if (isset($query['search'])) {
            // Same param name, so do nothing
        }
        if (isset($query['slugs'])) {
            $query['slug'] = $query['slugs'];
            unset($query['slugs']);
        }

        return $this->hooksAPI->applyFilters(
            'CMSAPI:taxonomies:query',
            $this->hooksAPI->applyFilters(
                'CMSAPI:categories:query',
                $query,
                $options
            ),
            $query,
            $options
        );
    }

    /**
     * @param string|int|object $catObjectOrID
     */
    public function getCategoryURL($catObjectOrID): string
    {
        return \get_term_link($catObjectOrID, $this->getCategoryTaxonomyName());
    }

    public function getCategoryBase()
    {
        $cmsService = CMSServiceFacade::getInstance();
        return $cmsService->getOption($this->getCategoryBaseOption());
    }

    public function setPostCategories($post_id, array $categories, bool $append = false)
    {
        return wp_set_post_terms($post_id, $categories, $this->getCategoryTaxonomyName(), $append);
    }

    // protected function returnCategoryObjectsOrIDs($categories, $options = []): array
    // {
    //     $return_type = $options['return-type'] ?? null;
    //     if ($return_type == ReturnTypes::IDS) {
    //         return array_map(
    //             function ($category) {
    //                 return $category->term_id;
    //             },
    //             $categories
    //         );
    //     }
    //     return $categories;
    // }
    // public function getCategoryCount($query, $options = []): int
    // {
    //     $options['return-type'] = ReturnTypes::IDS;
    //     // All results, no offset
    //     $query['number'] = 0;
    //     unset($query['offset']);
    //     return count($this->getCategories($query, $options));
    // }
    // public function getCustomPostCategories($post_id, array $options = []): array
    // {
    //     $query = [];
    //     if ($return_type = $options['return-type'] ?? null) {
    //         if ($return_type == ReturnTypes::IDS) {
    //             $query['fields'] = 'ids';
    //         } elseif ($return_type == ReturnTypes::SLUGS) {
    //             $query['fields'] = 'slugs';
    //         }
    //     }
    //     return (array) wp_get_post_categories($post_id, $query);
    // }
    // public function getCustomPostCategoryCount($query, $options = []): int
    // {
    //     $options['return-type'] = ReturnTypes::IDS;
    //     // All results, no offset
    //     $query['number'] = 0;
    //     unset($query['offset']);
    //     return count($this->getCustomPostCategories($query, $options));
    // }
    // public function getCategoryName($cat_id)
    // {
    //     return get_cat_name($cat_id);
    // }
    /**
     * @param string|int|object $catObjectOrID
     * @return object
     */
    protected function getCategoryFromObjectOrID($catObjectOrID)
    {
        return is_object($catObjectOrID) ?
            $catObjectOrID
            : \get_term($catObjectOrID, $this->getCategoryTaxonomyName());
    }

    /**
     * @param string|int|object $catObjectOrID
     */
    public function getCategorySlug($catObjectOrID): string
    {
        $category = $this->getCategoryFromObjectOrID($catObjectOrID);
        return $category->slug;
    }

    /**
     * @param string|int|object $catObjectOrID
     */
    public function getCategoryName($catObjectOrID): string
    {
        $category = $this->getCategoryFromObjectOrID($catObjectOrID);
        return $category->name;
    }

    /**
     * @param string|int|object $catObjectOrID
     * @return string|int|null
     */
    public function getCategoryParentID($catObjectOrID)
    {
        $category = $this->getCategoryFromObjectOrID($catObjectOrID);
        // If it has no parent, it is assigned 0. In that case, return null
        if ($parent = $category->parent) {
            return $parent;
        }
        return null;
    }

    /**
     * @param string|int|object $catObjectOrID
     */
    public function getCategoryDescription($catObjectOrID): string
    {
        $category = $this->getCategoryFromObjectOrID($catObjectOrID);
        return $category->description;
    }
    /**
     * @param string|int|object $catObjectOrID
     */
    public function getCategoryItemCount($catObjectOrID): int
    {
        $category = $this->getCategoryFromObjectOrID($catObjectOrID);
        return $category->count;
    }
}
