<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Result\Renderer\Export;

use VladimirPopov\WebForms\Model\ResourceModel\File\CollectionFactory;

class File extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected $_customerFactory;

    protected $_fieldFactory;

    protected $_storeManager;

    protected $fileCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \VladimirPopov\WebForms\Model\FieldFactory $fieldFactory,
        \VladimirPopov\WebForms\Model\ResourceModel\File\CollectionFactory $fileCollectionFactory,
        \Magento\Store\Model\StoreManager $storeManager,
        array $data = []
    )
    {
        $this->_customerFactory = $customerFactory;
        $this->_fieldFactory = $fieldFactory;
        $this->_storeManager = $storeManager;
        $this->fileCollectionFactory = $fileCollectionFactory;
        parent::__construct($context, $data);
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $field_id = str_replace('field_', '', $this->getColumn()->getIndex());

        $files = $this->fileCollectionFactory->create()
            ->addFilter('result_id', $row->getId())
            ->addFilter('field_id', $field_id);

        $output = '';
        /** @var \VladimirPopov\WebForms\Model\File $file */
        foreach ($files as $file) {
            if (file_exists($file->getFullPath())) {
                $output .= $file->getDownloadLink(true);
            }
        }

        return $output;
    }
}
