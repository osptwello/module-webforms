<?php


namespace VladimirPopov\WebForms\Model\ResourceModel\Result\Customer;


use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;

class Collection extends SearchResult
{
    /**
     * Constructor
     * Configures collection
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('VladimirPopov\WebForms\Model\Result', 'VladimirPopov\WebForms\Model\ResourceModel\Result');
    }

    public function _initSelect()
    {
        parent::_initSelect();
        $collection = $this->getSelect()
            ->joinLeft(
                ['webforms' => $this->getTable('webforms')],
                'main_table.webform_id = webforms.id',
                ['form' => 'name']
            );
        $this->addFilterToMap('id', 'main_table.id');
        $this->addFilterToMap('form', 'webforms.name');
        $this->addFilterToMap('customer_id', 'main_table.customer_id');

        return $collection;
    }
}