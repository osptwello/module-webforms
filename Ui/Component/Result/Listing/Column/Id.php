<?php

namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use VladimirPopov\WebForms\Model\ResourceModel\Message;

class Id extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * Message collection factory
     *
     * @var Message\CollectionFactory
     */
    protected $_messageCollectionFactory;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Message\CollectionFactory $messageCollectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        Message\CollectionFactory $messageCollectionFactory,
        array $components = [],
        array $data = []
    )
    {
        $this->urlBuilder = $urlBuilder;
        $this->_messageCollectionFactory = $messageCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $value = $item[$fieldName];
                $messages = $this->_messageCollectionFactory->create()->addFilter('result_id', $value)->count();
                $item['replied'] = $messages ? true : false;
                $item[$fieldName] = $this->prepareItem($fieldName, $item);
                $item['result_id'] = $value;
            }
        }

        return $dataSource;
    }

    protected function prepareItem($fieldName, array $item)
    {
        if ($item['replied']) {
            return '<div class="result-replied"><a href="' . $this->getReplyUrl($item['id']) . '">' .
                $item['id'] .
                '</a></div>';
        }
        return '<div class="result-not-replied"><a href="' . $this->getReplyUrl($item['id']) . '">' .
            $item['id'] .
            '</a></div>';
    }

    protected function getReplyUrl($resultId)
    {
        return $this->urlBuilder->getUrl(
            'webforms/result/reply',
            ['id' => $resultId, '_current' => true]
        );
    }
}