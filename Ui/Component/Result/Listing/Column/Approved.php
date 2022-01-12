<?php


namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;


use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use VladimirPopov\WebForms\Model\Result;
use VladimirPopov\WebForms\Model\ResultFactory;

class Approved extends Column
{

    /** @var ResultFactory */
    protected $resultFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ResultFactory $resultFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ResultFactory $resultFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->resultFactory = $resultFactory;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $statuses = $this->resultFactory->create()->getApprovalStatuses();
            foreach ($dataSource['data']['items'] as & $item) {
                $value = $item[$fieldName];
                $name = '';
                foreach ($statuses as $status_id => $status_name) {
                    if ($value == $status_id) {
                        $name = $status_name;
                        break;
                    }
                }
                $class = '';
                switch ($value) {
                    case Result::STATUS_PENDING:
                        $class = 'grid-severity-minor';
                        break;
                    case Result::STATUS_APPROVED:
                        $class = 'grid-severity-notice';
                        break;
                    case Result::STATUS_NOTAPPROVED:
                        $class = 'grid-severity-critical';
                        break;
                }
                $html = '<span class="' . $class . '"><span>' . $name . '</span></span>';
                $item[$fieldName] = $html;
            }
        }

        return $dataSource;
    }
}