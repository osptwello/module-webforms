<?php
namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

class Store extends \Magento\Store\Ui\Component\Listing\Column\Store
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['result_store_id'] = $item[$this->getData('name')];

            }
        }
        return parent::prepareDataSource($dataSource);
    }
}