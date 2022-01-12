<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Form;

class Export extends \Magento\Backend\App\Action
{
    protected $_workingDirectory;

    protected $formFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \VladimirPopov\WebForms\Model\FormFactory $formFactory
    )
    {
        $this->formFactory = $formFactory;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->formFactory->create();

            $model->load($id);

            $body = $model->toJson();

            $fileName = $model->getName() . '.json';
            $contentType = 'application/json';

            $this->getResponse()->setHttpResponseCode(
                200
            )->setHeader(
                'Pragma',
                'public',
                true
            )->setHeader(
                'Cache-Control',
                'must-revalidate, post-check=0, pre-check=0',
                true
            )->setHeader(
                'Content-type',
                $contentType,
                true
            );

            if (strlen($body)) {
                $this->getResponse()->setHeader('Content-Length', strlen($body));
            }

            $this->getResponse()->setHeader('Content-Disposition', 'attachment; filename="' . $fileName .'"');

            $this->getResponse()->clearBody();
            $this->getResponse()->sendHeaders();

            $this->_getSession()->writeClose();

            return $this->getResponse()->setBody($body);

        }
        return $resultRedirect->setPath('*/*/');
    }
}
