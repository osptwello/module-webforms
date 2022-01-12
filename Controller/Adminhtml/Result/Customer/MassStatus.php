<?php


namespace VladimirPopov\WebForms\Controller\Adminhtml\Result\Customer;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Component\MassAction\Filter;
use VladimirPopov\WebForms\Model\Result;
use VladimirPopov\WebForms\Model\ResultRepository;
use VladimirPopov\WebForms\Model\ResourceModel\Result\Customer\CollectionFactory;

class MassStatus extends Action
{

    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'VladimirPopov_WebForms::manage_forms';

    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $webformResultFactory;

    /**
     * @var ResultRepository
     */
    protected $resultRepository;

    /**
     * MassDelete constructor.
     * @param Context $context
     * @param CollectionFactory $webformResultFactory
     * @param ResultRepository $resultRepository
     * @param JsonFactory $jsonFactory
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        CollectionFactory $webformResultFactory,
        ResultRepository $resultRepository,
        JsonFactory $jsonFactory,
        Filter $filter
    )
    {
        parent::__construct($context);
        $this->webformResultFactory = $webformResultFactory;
        $this->resultRepository = $resultRepository;
        $this->filter            = $filter;
        $this->jsonFactory       = $jsonFactory;
    }

    public function execute(): Json
    {
        $customerData = $this->_session->getData('customer_data');

        $collection = $this->filter->getCollection($this->webformResultFactory->create());
        $error = false;

        try {
            if ($customerData && $customerData['customer_id']) {
                $collection->addFieldToFilter('customer_id', $customerData['customer_id']);
            } else {
                throw new Exception();
            }
            $collectionSize = $collection->getSize();

            if ($collectionSize < 1) {
                throw new NoSuchEntityException();
            }

            $newStatus = $this->getRequest()->getParam('status');

            /** @var Result $_result */
            foreach ($collection as $_result) {
                $result = $this->resultRepository->getById($_result->getId());
                $result->setApproved($newStatus);
                $this->resultRepository->save($result);
            }

            $message = __('A total of %1 record(s) have been updated.', $collectionSize);
        } catch (NoSuchEntityException $e) {
            $message = __('Please select item(s).');
            $error = true;
        } catch (LocalizedException $e) {
            $message = __($e->getMessage());
            $error = true;
        } catch (Exception $e) {
            $message = __('We can\'t mass update the results right now.');
            $error = true;
        }

        $resultJson = $this->jsonFactory->create();
        $resultJson->setData([
            'message' => $message,
            'error' => $error
        ]);

        return $resultJson;
    }

}
