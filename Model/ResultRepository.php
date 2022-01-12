<?php


namespace VladimirPopov\WebForms\Model;


use Exception;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use VladimirPopov\WebForms\Model\ResourceModel\Result as ResourceResult;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class ResultRepository
{
    /**
     * @var ResourceResult
     */
    protected $resource;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        ResourceResult $resource,
        ResultFactory $resultFactory,
        CollectionProcessorInterface $collectionProcessor
    )
    {
        $this->resource                = $resource;
        $this->resultFactory           = $resultFactory;
        $this->collectionProcessor     = $collectionProcessor;
    }

    /**
     * @param string $id
     * @return Result
     * @throws NoSuchEntityException
     */
    public function getById(string $id)
    {
        $result = $this->resultFactory->create();
        $this->resource->load($result, $id);
        if (!$result->getId()) {
            throw new NoSuchEntityException(__('Unable to find result with ID "%1"', $id));
        }
        return $result;
    }

    /**
     * @param Result $result
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(Result $result)
    {
        try {
            $this->resource->delete($result);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Save Serial data
     *
     * @param Result $result
     * @return Result
     * @throws CouldNotSaveException
     */
    public function save(Result $result)
    {
        try {
            $this->resource->save($result);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $result;
    }

}