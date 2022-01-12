<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Result;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

class MassStatus extends \Magento\Backend\App\Action
{
    const ID_FIELD = 'selected';

    const REDIRECT_URL = '*/*/';

    const MODEL = 'VladimirPopov\WebForms\Model\Result';

    protected $redirect_params = ['_current' => true];

    protected $webformsHelper;
    protected $formFactory;

    protected $webformResultFactory;

    public function __construct(
        Action\Context $context,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper,
        \VladimirPopov\WebForms\Model\FormFactory $formFactory,
        \VladimirPopov\WebForms\Model\ResultFactory $webformResultFactory
    )
    {
        $this->webformsHelper = $webformsHelper;
        $this->formFactory = $formFactory;
        $this->webformResultFactory = $webformResultFactory;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        if ($this->getRequest()->getParam('webform_id')) {
            return $this->webformsHelper->isAllowed($this->getRequest()->getParam('webform_id'));
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $Ids = [];
        if ($this->getRequest()->getParam('excluded') === 'false') {
            $webformId = $this->getRequest()->getParam('webform_id');

            if ($webformId) {
                $filters = $this->getRequest()->getParam('filters');
                /** @var \VladimirPopov\WebForms\Model\ResourceModel\Result\Collection $collection */
                $collection = $this->webformResultFactory->create()->getCollection()->addFilter('webform_id', $webformId);
                foreach ($filters as $fieldName => $value) {
                    if (strstr($fieldName, 'field_')) {
                        $fieldID = str_replace('field_', '', $fieldName);
                        $collection->addFieldFilter($fieldID, $value);
                    }
                }
                if (isset($filters['created_time'])) {
                    $from = $to = false;
                    if (!empty($filters['created_time']['from'])) $from = date('Y-m-d', strtotime($filters['created_time']['from'])) . ' 00:00:00';
                    if (!empty($filters['created_time']['to'])) $to = date('Y-m-d', strtotime($filters['created_time']['to'])) . ' 23:59:59';
                    if ($from)
                        $collection->addFieldToFilter('created_time', ['gteq' => $from]);
                    if ($to)
                        $collection->addFieldToFilter('created_time', ['lteq' => $to]);
                }
                if (isset($filters['id'])) {
                    $from = $to = false;
                    if (!empty($filters['id']['from'])) $from = $filters['id']['from'];
                    if (!empty($filters['id']['to'])) $to = $filters['id']['to'];
                    if ($from)
                        $collection->addFieldToFilter('id', ['gteq' => $from]);
                    if ($to)
                        $collection->addFieldToFilter('id', ['lteq' => $to]);
                }
                if (isset($filters['approved'])) {
                    $collection->addFilter('approved', $filters['approved']);
                }
                if (isset($filters['customer'])) {
                    $collection->addFieldToFilter('customer', ['like' => '%' . $filters['customer'] . '%']);
                }
                foreach ($collection as $result) {
                    $Ids[] = $result->getId();
                }
            }
        } else {
            $Ids = $this->getRequest()->getParam(static::ID_FIELD);
        }

        $status = $this->getRequest()->getParam('status');

        $formId = $this->getRequest()->getParam('webform_id');
        $modelForm = $this->formFactory->create()->load($formId);
        if (!is_array($Ids) || empty($Ids)) {
            $this->messageManager->addErrorMessage(__('Please select item(s).'));
        } else {
            try {
                foreach ($Ids as $id) {
                    $item = $this->webformResultFactory->create()->load($id);
                    $item->setApproved(intval($status))->save();

                    $this->_eventManager->dispatch('webforms_result_approve', array('result' => $item));

                    if ($modelForm->getEmailResultApproval()) {
                        $item->sendApprovalEmail();
                    }
                }
                $this->messageManager->addSuccessMessage(
                    __('Total of %1 result(s) have been updated.', count($Ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if ($this->getRequest()->getParam('customer_id')) {
            return $resultRedirect->setPath('customer/index/edit', [
                'id' => $this->getRequest()->getParam('customer_id')
            ]);
        }
        return $resultRedirect->setPath(static::REDIRECT_URL, ['webform_id' => $formId]);
    }
}
