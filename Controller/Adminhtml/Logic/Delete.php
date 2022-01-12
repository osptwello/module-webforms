<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Logic;

use Magento\Backend\App\Action;

class Delete extends \Magento\Backend\App\Action
{

    protected $webformsHelper;

    protected $logicFactory;

    protected $fieldFactory;

    public function __construct(
        Action\Context $context,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper,
        \VladimirPopov\WebForms\Model\LogicFactory $logicFactory,
        \VladimirPopov\WebForms\Model\FieldFactory $fieldFactory
    )
    {
        $this->webformsHelper = $webformsHelper;
        $this->logicFactory = $logicFactory;
        $this->fieldFactory = $fieldFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->logicFactory->create()->load($id);
        $fieldId = $model->getFieldId();
        if ($fieldId) {
            $model = $this->fieldFactory->create()->load($fieldId);
            return $this->webformsHelper->isAllowed($model->getWebformId());
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
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
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                // init model and delete
                $model = $this->logicFactory->create();
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('The logic has been deleted.'));
                if ($this->getRequest()->getParam('webform_id')) {
                    return $resultRedirect->setPath('*/form/edit', ['id' => $this->getRequest()->getParam('webform_id')]);
                }
                return $resultRedirect->setPath('*/field/edit', ['id' => $model->getFieldId()]);
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit logic
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a logic to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/form/');
    }
}
