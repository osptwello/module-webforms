<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Logic;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
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
        $data = $this->getRequest()->getPostValue('logic');
        $fieldId = $model->getFieldId() ? $model->getFieldId() : $data['field_id'];
        if ($fieldId) {
            $model = $this->fieldFactory->create()->load($fieldId);
            return $this->webformsHelper->isAllowed($model->getWebformId());
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $store = $this->getRequest()->getParam('store');
        $data = $this->getRequest()->getPostValue('logic');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->logicFactory->create();

            !empty($data['id']) ? $id = $data['id'] : $id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
                if ($store) {
                    unset($data['id']);
                    unset($data['field_id']);
                    $model->saveStoreData($store, $data);
                }
            }

            $this->_eventManager->dispatch(
                'webforms_logic_prepare_save',
                ['logic' => $model, 'request' => $this->getRequest()]
            );

            try {
                if (!$store) $model->setData($data)->save();

                $this->messageManager->addSuccessMessage(__('You saved this logic.'));
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                if ($this->getRequest()->getParam('webform_id'))
                    return $resultRedirect->setPath('*/form/edit', ['id' => $this->getRequest()->getParam('webform_id'), 'active_tab' => 'logic_section', 'store' => $store]);
                return $resultRedirect->setPath('*/field/edit', ['id' => $model->getFieldId(), 'active_tab' => 'logic_section', 'store' => $store]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the logic.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $id, 'field_id' => $this->getRequest()->getParam('field_id'), 'store' => $store]);
        }
        return $resultRedirect->setPath('*/form/');
    }
}
