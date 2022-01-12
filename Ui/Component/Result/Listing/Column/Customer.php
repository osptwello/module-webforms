<?php
namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use function htmlspecialchars;

class Customer extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_urlBuilder = $urlBuilder;
        $this->customerFactory = $customerFactory;

    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $value = $item['customer_id'];
                if ($value) {
                    $customer = $this->customerFactory->create()->load($value);
                    if ($customer && $customer->getId())
                        $html = "<a href='" . $this->getCustomerUrl($customer->getId()) . "' target='_blank'>" . htmlspecialchars($customer->getName()) . "</a>";
                    else
                        $html = __('Guest');
                } else {
                    $html = __('Guest');
                }
                $item[$fieldName] = $html;
            }
        }

        return $dataSource;
    }

    public function getCustomerUrl($customer_id)
    {

        return $this->getUrl('customer/index/edit', array('id' => $customer_id, '_current' => false));
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
