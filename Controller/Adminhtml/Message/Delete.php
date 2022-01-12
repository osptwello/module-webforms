<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Message;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{

    protected $webformsHelper;
    protected $webformResultFactory;
    protected $_messageFactory;

    public function __construct(
        Action\Context $context,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper,
        \VladimirPopov\WebForms\Model\MessageFactory $messageFactory,
        \VladimirPopov\WebForms\Model\ResultFactory $webformResultFactory
    ) {
        $this->webformsHelper = $webformsHelper;
        $this->webformResultFactory = $webformResultFactory;
        $this->_messageFactory = $messageFactory;
        parent::__construct($context);
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
        $model = $this->_messageFactory->create();
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
        if ($id) {
            // init model and delete
            $model = $this->_initMessage();
            $model->delete();
        }
    }
}
