<?php
namespace VladimirPopov\WebForms\Cron;
use Psr\Log\LogLevel;

class Purge
{
    protected $_logger;

    protected $scopeConfig;

    protected $formCollectionFactory;

    protected $resultCollectionFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \VladimirPopov\WebForms\Model\ResourceModel\Form\CollectionFactory $formCollectionFactory,
        \VladimirPopov\WebForms\Model\ResourceModel\Result\CollectionFactory $resultCollectionFactory
    ) {
        $this->_logger = $logger;
        $this->scopeConfig = $scopeConfig;
        $this->formCollectionFactory = $formCollectionFactory;
        $this->resultCollectionFactory = $resultCollectionFactory;

    }

    public function execute(){
        $purge_enabled = $this->scopeConfig->getValue('webforms/gdpr/purge_enable', \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE);

        $forms = $this->formCollectionFactory->create();

        foreach ($forms as $form) {
            if (
                $form->getData('purge_enable') == 1
                || ($form->getData('purge_enable') == -1 && $purge_enabled)
            ) {
                if ($form->getData('purge_enable') == -1)
                    $purge_period = intval($this->scopeConfig->getValue('webforms/gdpr/purge_period',\Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE));
                else
                    $purge_period = intval($form->getData('purge_period'));

                if ($purge_period > 0) {
                    $date = date('Y-m-d', strtotime('-' . $purge_period . ' day'));

                    $collection = $this->resultCollectionFactory->create()
                        ->addFilter('webform_id', $form->getId())
                        ->addFieldToFilter('created_time', array('lt' => $date));
                    foreach ($collection as $result) {
                        $result->delete();
                    }
                }
            }
        }
    }
}