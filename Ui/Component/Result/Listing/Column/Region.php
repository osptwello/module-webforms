<?php

namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use function htmlentities;
use function json_decode;

class Region extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected $regCollectionFactory;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param CollectionFactory $regCollectionFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        CollectionFactory $regCollectionFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->regCollectionFactory = $regCollectionFactory;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item[$fieldName])) {
                    $regionInfo = json_decode($item[$fieldName], true);
                    if (isset($regionInfo['region']))
                        $item[$fieldName] = htmlentities($regionInfo['region']);

                    if (isset($regionInfo['region_id'])) {
                        $collection = $this->regCollectionFactory->create()->addFilter('main_table.region_id', $regionInfo['region_id']);
                        $region     = $collection->getFirstItem();
                        if ($region->getName())
                            $item[$fieldName] = htmlentities($region->getName());
                    }
                }
            }
        }

        return $dataSource;
    }
}
