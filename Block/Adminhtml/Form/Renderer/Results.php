<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Form\Renderer;

class Results extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_resultCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \VladimirPopov\WebForms\Model\ResourceModel\Result\CollectionFactory $resultCollectionFactory,
        array $data = []
    )
    {
        $this->_resultCollectionFactory = $resultCollectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * Render the grid cell value
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $value = $this->_resultCollectionFactory->create()->addFilter('webform_id',$row->getId())->getSize();

        return '<nobr>'.$value.' [ <a href="#" style="text-decoration:none" onclick="setLocation(\''.$this->getResultsUrl($row).'\')">'.__('View').'</a> ]</nobr>';
    }

    public function getResultsUrl(\Magento\Framework\DataObject $row)
    {
        return $this->getUrl('*/result',array('webform_id'=>$row->getId()));
    }
}
