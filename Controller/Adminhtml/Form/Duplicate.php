<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Form;

use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Authorization;

class Duplicate extends \Magento\Backend\App\Action
{
    protected $roleLocator;

    protected $_rulesFactory;

    protected $_rulesCollectionFactory;

    protected $_aclBuilder;

    protected $authSession;

    protected $webformsHelper;

    protected $formFactory;

    public function __construct(
        Action\Context $context,
        Authorization\RoleLocator $roleLocator,
        RulesFactory $rulesFactory,
        \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,
        \Magento\Framework\Acl\Builder $aclBuilder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \VladimirPopov\WebForms\Helper\Data $webformsHelper,
        \VladimirPopov\WebForms\Model\FormFactory $formFactory
    )
    {
        $this->roleLocator = $roleLocator;
        $this->_rulesFactory = $rulesFactory;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_aclBuilder = $aclBuilder;
        $this->authSession = $authSession;
        $this->webformsHelper = $webformsHelper;
        $this->formFactory = $formFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        if ($this->getRequest()->getParam('id')) {
            return $this->webformsHelper->isAllowed($this->getRequest()->getParam('id'));
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
                $model = $this->formFactory->create();
                $model->load($id);
                $newForm = $model->duplicate();

                // update role permissions
                if (!$this->_authorization->isAllowed('Magento_Backend::all')) {
                    $this->_rulesFactory->create()->setData([
                        'role_id' => $this->roleLocator->getAclRoleId(),
                        'resource_id' => 'VladimirPopov_WebForms::form' . $newForm->getId(),
                        'permission' => 'allow'
                    ])->save();
                }
                // display success message
                $this->messageManager->addSuccessMessage(__('The form has been duplicated.'));
                return $resultRedirect->setPath('*/form/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a form to duplicate.'));
        // go to grid
        return $resultRedirect->setPath('*/form/');
    }
}
