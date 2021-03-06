<?php

declare (strict_types=1);
namespace PoPSchema\PostCategories\FieldResolvers;

use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoPSchema\Categories\FieldResolvers\AbstractCustomPostQueryableFieldResolver;
use PoPSchema\PostCategories\ComponentContracts\PostCategoryAPISatisfiedContractTrait;
use PoPSchema\PostCategories\ModuleProcessors\PostCategoryFieldDataloadModuleProcessor;
use PoPSchema\Posts\TypeResolvers\PostTypeResolver;
class PostQueryableFieldResolver extends AbstractCustomPostQueryableFieldResolver
{
    use PostCategoryAPISatisfiedContractTrait;
    public function getClassesToAttachTo() : array
    {
        return [PostTypeResolver::class];
    }
    public function getSchemaFieldDescription(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        $descriptions = ['categories' => $this->translationAPI->__('Categories added to this post', 'post-categories'), 'categoryCount' => $this->translationAPI->__('Number of categories added to this post', 'post-categories'), 'categoryNames' => $this->translationAPI->__('Names of the categories added to this post', 'post-categories')];
        return $descriptions[$fieldName] ?? parent::getSchemaFieldDescription($typeResolver, $fieldName);
    }
    protected function getFieldDefaultFilterDataloadingModule(TypeResolverInterface $typeResolver, string $fieldName, array $fieldArgs = []) : ?array
    {
        switch ($fieldName) {
            case 'categoryCount':
                return [PostCategoryFieldDataloadModuleProcessor::class, PostCategoryFieldDataloadModuleProcessor::MODULE_DATALOAD_RELATIONALFIELDS_CATEGORYCOUNT];
            case 'categoryNames':
                return [PostCategoryFieldDataloadModuleProcessor::class, PostCategoryFieldDataloadModuleProcessor::MODULE_DATALOAD_RELATIONALFIELDS_CATEGORYLIST];
        }
        return parent::getFieldDefaultFilterDataloadingModule($typeResolver, $fieldName, $fieldArgs);
    }
}
