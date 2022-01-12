<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Fieldset;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $webformsHelper;

    protected $fieldsetFactory;

    protected $formFactory;


    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper,
        \VladimirPopov\WebForms\Model\FieldsetFactory $fieldsetFactory,
        \VladimirPopov\WebForms\Model\FormFactory $formFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        $this->webformsHelper = $webformsHelper;
        $this->fieldsetFactory = $fieldsetFactory;
        $this->formFactory = $formFactory;

        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        if($this->getRequest()->getParam('id')){
            $model = $this->fieldsetFactory->create()->load($this->getRequest()->getParam('id'));
            return $this->webformsHelper->isAllowed($model->getWebformId());
        }
        if($this->getRequest()->getParam('webform_id')){
            return $this->webformsHelper->isAllowed($this->getRequest()->getParam('webform_id'));
        }
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('VladimirPopov_WebForms::manage_forms')
            ->addBreadcrumb(__('Web-forms'), __('Web-forms'))
            ->addBreadcrumb(__('Manage Forms'), __('Manage Forms'));
        return $resultPage;
    }

    /**
     * Edit field
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('id');
        $formId = $this->getRequest()->getParam('webform_id');
        $model = $this->fieldsetFactory->create()->setStoreId($this->getRequest()->getParam('store'));
        $modelForm = $this->formFactory->create()->setStoreId($this->getRequest()->getParam('store'));

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            $formId = $model->getWebformId();
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This fieldset no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/*/');
            }
        }

        if ($formId) {
            $model->setWebformId($formId);
            $modelForm->load($formId);
            if (!$modelForm->getId()) {
                $this->messageManager->addErrorMessage(__('This form no longer exists.'));
                /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();

                return $resultRedirect->setPath('*/form/');
            }
        } else {
            $this->messageManager->addErrorMessage(__('Form identifier is not specified.'));
            /** \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setPath('*/form/');
        }

        // 3. Set entered data if was error when we do save
        $data = $this->_getSession()->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register models to use later in blocks
        $this->_coreRegistry->register('webforms_fieldset', $model);
        $this->_coreRegistry->register('webforms_form', $modelForm);

        // 5. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Fieldset') : __('New Fieldset'),
            $id ? __('Edit Fieldset') : __('New Fieldset')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Fieldsets'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getName() : __('New Fieldset'));

        // 6. Remove store view switcher if the entity is new
        if(!$id)
            $resultPage->getLayout()->getBlock('store_switcher')->setTemplate(false);

        return $resultPage;
    }
}
