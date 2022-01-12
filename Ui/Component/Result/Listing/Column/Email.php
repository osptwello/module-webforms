<?php

namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\StoreManager;
use function htmlspecialchars;

class Email extends \Magento\Ui\Component\Listing\Columns\Column
{

    /** @var StoreManager */
    protected $storeManager;

    /** @var CustomerFactory */
    protected $customerFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        StoreManager $storeManager,
        CustomerFactory $customerFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->storeManager    = $storeManager;
        $this->customerFactory = $customerFactory;
        $this->urlBuilder      = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                $value = empty($item[$fieldName]) ? false : $item[$fieldName];
                if ($value && isset($item['result_store_id'])) {
                    $websiteId = false;
                    try {
                        $website = $this->storeManager->getStore($item['result_store_id'])->getWebsite();
                        if ($website) {
                            $websiteId = $website->getId();
                        }
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    }
                    $customer         = $this->customerFactory->create()->setData('website_id', $websiteId)->loadByEmail($value);
                    $item[$fieldName] = $this->prepareItem($value, $customer);
                }
            }
        }

        return $dataSource;
    }

    protected function prepareItem($value, $customer)
    {
        if ($customer && $customer->getId()) {
            return htmlspecialchars($value) . ' [<a href=\'' . $this->getCustomerUrl($customer->getId()) . '\' target=\'_blank\'>' . htmlspecialchars($customer->getName()) . '</a>]';
        }
        return htmlspecialchars($value);
    }

    public function getCustomerUrl($customerId)
    {
        return $this->urlBuilder->getUrl('customer/index/edit', array('id' => $customerId, '_current' => false));
    }
}
