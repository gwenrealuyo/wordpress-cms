<?php

declare (strict_types=1);
namespace PoPSchema\Comments\TypeDataLoaders;

use PoP\ComponentModel\Instances\InstanceManagerInterface;
use PoP\ComponentModel\TypeDataLoaders\AbstractTypeQueryableDataLoader;
use PoP\Hooks\HooksAPIInterface;
use PoP\LooseContracts\NameResolverInterface;
use PoPSchema\Comments\Constants\Params;
use PoPSchema\Comments\Constants\Status;
use PoPSchema\Comments\ModuleProcessors\CommentRelationalFieldDataloadModuleProcessor;
use PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface;
use PoPSchema\SchemaCommons\DataLoading\ReturnTypes;
class CommentTypeDataLoader extends AbstractTypeQueryableDataLoader
{
    /**
     * @var \PoPSchema\Comments\TypeAPIs\CommentTypeAPIInterface
     */
    protected $commentTypeAPI;
    public function __construct(HooksAPIInterface $hooksAPI, InstanceManagerInterface $instanceManager, NameResolverInterface $nameResolver, CommentTypeAPIInterface $commentTypeAPI)
    {
        $this->commentTypeAPI = $commentTypeAPI;
        parent::__construct($hooksAPI, $instanceManager, $nameResolver);
    }
    public function getFilterDataloadingModule() : ?array
    {
        return [CommentRelationalFieldDataloadModuleProcessor::class, CommentRelationalFieldDataloadModuleProcessor::MODULE_DATALOAD_RELATIONALFIELDS_COMMENTS];
    }
    public function getObjects(array $ids) : array
    {
        $query = ['include' => $ids];
        return $this->commentTypeAPI->getComments($query);
    }
    public function getQuery($query_args) : array
    {
        $query = parent::getQuery($query_args);
        $query['status'] = Status::APPROVED;
        // $query['type'] = 'comment'; // Only comments, no trackbacks or pingbacks
        $query['customPostID'] = $query_args[Params::COMMENT_POST_ID];
        return $query;
    }
    public function getDataFromIdsQuery(array $ids) : array
    {
        $query = array();
        $query['include'] = $ids;
        return $query;
    }
    public function executeQuery($query, array $options = [])
    {
        return $this->commentTypeAPI->getComments($query, $options);
    }
    public function executeQueryIds($query) : array
    {
        $options = ['return-type' => ReturnTypes::IDS];
        return (array) $this->executeQuery($query, $options);
    }
}
