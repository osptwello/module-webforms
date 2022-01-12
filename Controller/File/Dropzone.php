<?php

namespace VladimirPopov\WebForms\Controller\File;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Dropzone extends Action
{
    protected $fieldFactory;

    protected $dropzoneFactory;

    protected $storeManager;

    protected $_jsonEncoder;

    protected $resultHttpFactory;

    public function __construct(
        Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\App\Response\HttpFactory $resultHttpFactory,
        \VladimirPopov\WebForms\Model\FieldFactory $fieldFactory,
        \VladimirPopov\WebForms\Model\DropzoneFactory $dropzoneFactory
    )
    {
        $this->fieldFactory = $fieldFactory;
        $this->dropzoneFactory = $dropzoneFactory;
        $this->storeManager = $storeManager;
        $this->_jsonEncoder = $jsonEncoder;
        $this->resultHttpFactory = $resultHttpFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = array();
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
        $json = $this->_jsonEncoder->encode($result);
        $resultHttp = $this->resultHttpFactory->create();
        $resultHttp->setNoCacheHeaders();
        $resultHttp->setHeader('Content-Type', 'text/plain', true);
        $resultHttp->setHeader('X-Robots-Tag', 'noindex', true);
        return $resultHttp->setContent($json);
    }
}