<?php

namespace VladimirPopov\WebForms\Model\ResourceModel\Dropzone;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';


    protected function _construct()
    {
        $this->_init('VladimirPopov\WebForms\Model\Dropzone', 'VladimirPopov\WebForms\Model\ResourceModel\Dropzone');
    }

}