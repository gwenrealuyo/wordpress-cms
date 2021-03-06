<?php

declare (strict_types=1);
namespace PoPSchema\CustomPostMedia\FieldResolvers;

use PoP\ComponentModel\FieldResolvers\AbstractDBDataFieldResolver;
use PoP\ComponentModel\FieldResolvers\FieldSchemaDefinitionResolverInterface;
use PoP\ComponentModel\HelperServices\SemverHelperServiceInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\Schema\FieldQueryInterpreterInterface;
use PoP\ComponentModel\TypeResolvers\TypeResolverInterface;
use PoP\Engine\CMS\CMSServiceInterface;
use PoP\Hooks\HooksAPIInterface;
use PoP\LooseContracts\NameResolverInterface;
use PoP\Translation\TranslationAPIInterface;
use PoPSchema\CustomPostMedia\FieldInterfaceResolvers\SupportingFeaturedImageFieldInterfaceResolver;
use PoPSchema\CustomPostMedia\TypeAPIs\CustomPostMediaTypeAPIInterface;
use PoPSchema\CustomPosts\FieldInterfaceResolvers\IsCustomPostFieldInterfaceResolver;
class CustomPostFieldResolver extends AbstractDBDataFieldResolver
{
    /**
     * @var \PoPSchema\CustomPostMedia\TypeAPIs\CustomPostMediaTypeAPIInterface
     */
    protected $customPostMediaTypeAPI;
    public function __construct(TranslationAPIInterface $translationAPI, HooksAPIInterface $hooksAPI, InstanceManagerInterface $instanceManager, FieldQueryInterpreterInterface $fieldQueryInterpreter, NameResolverInterface $nameResolver, CMSServiceInterface $cmsService, SemverHelperServiceInterface $semverHelperService, CustomPostMediaTypeAPIInterface $customPostMediaTypeAPI)
    {
        $this->customPostMediaTypeAPI = $customPostMediaTypeAPI;
        parent::__construct($translationAPI, $hooksAPI, $instanceManager, $fieldQueryInterpreter, $nameResolver, $cmsService, $semverHelperService);
    }
    public function getClassesToAttachTo() : array
    {
        return [IsCustomPostFieldInterfaceResolver::class];
    }
    public function getImplementedFieldInterfaceResolverClasses() : array
    {
        return [SupportingFeaturedImageFieldInterfaceResolver::class];
    }
    public function getFieldNamesToResolve() : array
    {
        return ['hasFeaturedImage', 'featuredImage'];
    }
    /**
     * By returning `null`, the schema definition comes from the interface
     */
    public function getSchemaDefinitionResolver(TypeResolverInterface $typeResolver) : ?FieldSchemaDefinitionResolverInterface
    {
        return null;
    }
    /**
     * @param array<string, mixed> $fieldArgs
     * @param array<string, mixed>|null $variables
     * @param array<string, mixed>|null $expressions
     * @param array<string, mixed> $options
     * @return mixed
     * @param object $resultItem
     */
    public function resolveValue(TypeResolverInterface $typeResolver, $resultItem, string $fieldName, array $fieldArgs = [], ?array $variables = null, ?array $expressions = null, array $options = [])
    {
        $post = $resultItem;
        switch ($fieldName) {
            case 'hasFeaturedImage':
                return $this->customPostMediaTypeAPI->hasCustomPostThumbnail($typeResolver->getID($post));
            case 'featuredImage':
                return $this->customPostMediaTypeAPI->getCustomPostThumbnailID($typeResolver->getID($post));
        }
        return parent::resolveValue($typeResolver, $resultItem, $fieldName, $fieldArgs, $variables, $expressions, $options);
    }
    public function resolveFieldTypeResolverClass(TypeResolverInterface $typeResolver, string $fieldName) : ?string
    {
        switch ($fieldName) {
            case 'featuredImage':
                /**
                 * @var SupportingFeaturedImageFieldInterfaceResolver
                 */
                $fieldInterfaceResolver = $this->instanceManager->getInstance(SupportingFeaturedImageFieldInterfaceResolver::class);
                return $fieldInterfaceResolver->getFieldTypeResolverClass($fieldName);
        }
        return parent::resolveFieldTypeResolverClass($typeResolver, $fieldName);
    }
}
