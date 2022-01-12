<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Message;

class Email extends \Magento\Backend\App\Action
{
    protected $resultJsonFactory;

    protected $_jsonEncoder;

    protected $webformsHelper;

    protected $webformResultFactory;

    protected $messageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper,
        \VladimirPopov\WebForms\Model\ResultFactory $webformResultFactory,
        \VladimirPopov\WebForms\Model\MessageFactory $messageFactory
    )
    {
        parent::__construct($context);
        $this->_jsonEncoder = $jsonEncoder;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->webformsHelper = $webformsHelper;
        $this->webformResultFactory = $webformResultFactory;
        $this->messageFactory = $messageFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        $model = $this->_initMessage();
        $result = $this->webformResultFactory->create();
        $result->load($model->getResultId());
        return $this->webformsHelper->isAllowed($result->getWebformId());
    }

    protected function _initMessage()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->messageFactory->create();
        $model->load($id);
        return $model;
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('id');
        $result['success'] = false;
        if ($id) {
            // init model and delete
            $model = $this->_initMessage();
            $model->sendEmail();
            $result['success'] = true;
            // display success message
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $json = $this->_jsonEncoder->encode($result);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setJsonData($json);
    }
}
