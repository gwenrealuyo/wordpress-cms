<?php

declare (strict_types=1);
namespace PoPSchema\Taxonomies\TypeAPIs;

/**
 * Methods to interact with the Type, to be implemented by the underlying CMS
 */
interface TaxonomyTypeAPIInterface
{
    /**
     * Retrieves the taxonomy name of the object ("post_tag", "category", etc)
     * @param string|int|object $termObjectOrID
     */
    public function getTermTaxonomyName($termObjectOrID) : string;
}
