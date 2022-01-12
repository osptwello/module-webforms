<?php


namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;


use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use VladimirPopov\WebForms\Model\Result;
use VladimirPopov\WebForms\Model\ResultFactory;

class Subject extends Column
{

    /** @var ResultFactory */
    protected $resultFactory;

    /** @var UrlInterface */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param ResultFactory $resultFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        ResultFactory $resultFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->resultFactory = $resultFactory;
        $this->urlBuilder = $urlBuilder;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $field_id = 'id';
            $fieldName = $this->getData('name');
            $customer_id = $this->getData('customer_id');
            foreach ($dataSource['data']['items'] as & $item) {
                $id = $item[$field_id];

                /** @var Result $result */
                $result = $this->resultFactory->create()->load($id);
                $subject = $result->getEmailSubject();
                $title = str_replace("'", "\'", $subject);
                $url = $this->urlBuilder->getUrl('webforms/result/popup', array('id' => $id, 'customer_id' => $customer_id));
                $html = '<a href="javascript:Admin_JsWebFormsResultModal(' . '\'' . $title . '\'' . ',' . '\'' . $url . '\'' . ')">' . $subject . '</a>';
                $item[$fieldName] = $html;
            }
        }

        return $dataSource;
    }
}