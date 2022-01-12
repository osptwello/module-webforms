<?php
namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class Action extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as &$item) {

                $item[$this->getData('name')]['print'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'webforms/result/print',
                        ['id' => $item['result_id']]
                    ),
                    'label' => __('Print'),
                    'hidden' => false,
                ];

                $item[$this->getData('name')]['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'webforms/result/edit',
                        ['id' => $item['result_id'], '_current' => true]
                    ),
                    'label' => __('Edit'),
                    'hidden' => false,
                ];

                $item[$this->getData('name')]['reply'] = [
                    'href' => $this->urlBuilder->getUrl(
                        'webforms/result/reply',
                        ['id' => $item['result_id'], '_current' => true]
                    ),
                    'label' => __('Reply'),
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}