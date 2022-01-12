<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Result;

class SetStatus extends \Magento\Backend\App\Action
{
    const ID_FIELD = 'id';

    const MODEL = 'VladimirPopov\WebForms\Model\Result';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected $_jsonEncoder;

    protected $formFactory;

    protected $webformResultFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Json\Encoder $jsonEncoder,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \VladimirPopov\WebForms\Model\FormFactory $formFactory,
        \VladimirPopov\WebForms\Model\ResultFactory $webformResultFactory
    )
    {
        parent::__construct($context);
        $this->_jsonEncoder = $jsonEncoder;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->webformResultFactory = $webformResultFactory;
        $this->formFactory = $formFactory;
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
        $id = $this->getRequest()->getParam(static::ID_FIELD);
        $status = $this->getRequest()->getParam('status');

        /** @var \VladimirPopov\WebForms\Model\Result $item */
        $item = $this->webformResultFactory->create()->load($id);
        $item->setApproved(intval($status))->save();

        $formId = $item->getWebformId();
        $modelForm = $this->formFactory->create()->load($formId);

        $this->_eventManager->dispatch('webforms_result_approve', array('result' => $item));

        if ($modelForm->getEmailResultApproval()) {
            $item->sendApprovalEmail();
        }

        $response = [
            'text' => $item->getStatusName(),
            'status' => $item->getApproved()
        ];
        $json = $this->_jsonEncoder->encode($response);
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setJsonData($json);
    }
}
