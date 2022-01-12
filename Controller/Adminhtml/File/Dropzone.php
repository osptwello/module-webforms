<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\File;

class Dropzone extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected $_jsonEncoder;

    protected $_customerCollectionFactory;

    protected $fieldFactory;
    protected $dropzoneFactory;
    protected $storeManager;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
        \VladimirPopov\WebForms\Model\FieldFactory $fieldFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \VladimirPopov\WebForms\Model\DropzoneFactory $dropzoneFactory
    )
    {
        parent::__construct($context);
        $this->_jsonEncoder = $jsonEncoder;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->fieldFactory = $fieldFactory;
        $this->dropzoneFactory = $dropzoneFactory;
        $this->storeManager = $storeManager;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = [];
        $result['hash'] = '';
        $result['error'] = '';

        $uploaded_files = array();
        $file_id = $this->getRequest()->getParam('file_id');
        $field_id = str_replace('file_', '', $file_id);
        $field = $this->fieldFactory->create()->setStoreId($this->storeManager->getStore()->getId())->load($field_id);
        $uploader = new \Zend_Validate_File_Upload;
        $valid = $uploader->isValid($file_id);
        if ($valid) {
            $file = $uploader->getFiles($file_id);
            $uploaded_files[$file_id] = $file[$file_id];
            $result['error'] = $field->validate($file[$file_id]);
        }
        if (!$result['error']) {
            $hash = $this->dropzoneFactory->create()->upload($uploaded_files);
            $result['hash'] = $hash;
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $json = $this->_jsonEncoder->encode($result);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setJsonData($json);
    }
}
