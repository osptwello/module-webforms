<?php
namespace VladimirPopov\WebForms\Ui\Component\Result\Listing\Column;

class Password extends \Magento\Ui\Component\Listing\Columns\Column
{

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                if(isset($item[$fieldName])) {
                    $item[$fieldName] = '•••••••••';
                }
            }
        }

        return $dataSource;
    }
}
