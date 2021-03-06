<?php

declare (strict_types=1);
namespace PoPSchema\UserState\Hooks;

use PoP\Hooks\AbstractHookSet;
use PoP\Hooks\HooksAPIInterface;
use PoP\Translation\TranslationAPIInterface;
use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoPSchema\UserState\FieldResolvers\GlobalFieldResolver;
class DBEntriesHookSet extends AbstractHookSet
{
    /**
     * @var \PoPSchema\UserState\FieldResolvers\GlobalFieldResolver
     */
    protected $globalFieldResolver;
    public function __construct(HooksAPIInterface $hooksAPI, TranslationAPIInterface $translationAPI, InstanceManagerInterface $instanceManager, GlobalFieldResolver $globalFieldResolver)
    {
        $this->globalFieldResolver = $globalFieldResolver;
        parent::__construct($hooksAPI, $translationAPI, $instanceManager);
    }
    protected function init() : void
    {
        $this->hooksAPI->addFilter('PoP\\ComponentModel\\Engine:moveEntriesUnderDBName:dbName-dataFields', array($this, 'moveEntriesUnderDBName'), 10, 1);
    }
    public function moveEntriesUnderDBName(array $dbname_datafields) : array
    {
        $dbname_datafields['userstate'] = $this->hooksAPI->applyFilters('PoPSchema\\UserState\\DataloaderHooks:metaFields', $this->globalFieldResolver->getFieldNamesToResolve());
        return $dbname_datafields;
    }
}
