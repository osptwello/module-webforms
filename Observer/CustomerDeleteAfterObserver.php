<?php
namespace VladimirPopov\WebForms\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerDeleteAfterObserver implements ObserverInterface
{
    protected $scopeConfig;

    protected $resultCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \VladimirPopov\WebForms\Model\ResourceModel\Result\CollectionFactory $resultCollectionFactory
    ) {
        $this->resultCollectionFactory = $resultCollectionFactory;
        $this->scopeConfig = $scopeConfig;
    }

    public function execute(Observer $observer)
    {
        if($this->scopeConfig->getValue('webforms/gdpr/purge_data_on_account_delete',\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE)){
            $customer = $observer->getCustomer();
            $collection = $this->resultCollectionFactory->create()->addFilter('customer_id', $customer->getId());
            foreach ($collection as $result)
                $result->delete();
        }
    }
}