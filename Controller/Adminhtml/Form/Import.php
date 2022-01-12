<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Form;

use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\Model\Authorization;

class Import extends \Magento\Backend\App\Action
{
    protected $_workingDirectory;

    protected $_rulesCollectionFactory;

    protected $roleLocator;

    protected $_rulesFactory;

    protected $_cache;

    protected $authSession;

    protected $_aclBuilder;

    protected $formFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Authorization\RoleLocator $roleLocator,
        RulesFactory $rulesFactory,
        \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,
        \Magento\Framework\Acl\Builder $aclBuilder,
        \Magento\Backend\Model\Auth\Session $authSession,
    \VladimirPopov\WebForms\Model\FormFactory $formFactory
    )
    {
        $this->roleLocator = $roleLocator;
        $this->_rulesFactory = $rulesFactory;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_aclBuilder = $aclBuilder;
        $this->authSession = $authSession;
        $this->formFactory = $formFactory;

        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('VladimirPopov_WebForms::manage_forms');
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        $upload = new \Zend_Validate_File_Upload();
        $file = $upload->getFiles('import_form');

        $model = $this->formFactory->create();

        if ($file) {
            $importData = file_get_contents($file['import_form']['tmp_name']);

            $parse = $model->parseJson($importData);

            if (empty($parse['errors'])) {
                $model->import($importData);
                if ($model->getId()) {
                    // update role permissions
                    if(!$this->_authorization->isAllowed('Magento_Backend::all')){
                        $collection = $this->_rulesCollectionFactory->create()
                            ->addFilter('role_id', $this->roleLocator->getAclRoleId())
                            ->addFilter('resource_id', 'VladimirPopov_WebForms::form'.$model->getId())
                            ->addFilter('permission', 'allow');
                        if($collection->count() === 0) {
                            $this->_rulesFactory->create()->setData([
                                'role_id' => $this->roleLocator->getAclRoleId(),
                                'resource_id' => 'VladimirPopov_WebForms::form' . $model->getId(),
                                'permission' => 'allow'
                            ])->save();
                        }
                        $this->authSession->setAcl($this->_aclBuilder->getAcl());
                    }
                    $this->messageManager->addSuccessMessage(__('Form "%1" successfully imported.', $model->getName()));
                } else {
                    $this->messageManager->addErrorMessage(__('Unknown error happened during import operation.'));
                }
            } else {
                foreach ($parse['errors'] as $error) {
                    $this->messageManager->addErrorMessage($error);
                }
            }

            if (!empty($parse['warnings'])) {
                foreach ($parse['warnings'] as $warning) {
                    $this->messageManager->addWarningMessage($warning);
                }
            }

            return $this->_redirect('*/*/index');
        }

        $this->messageManager->addErrorMessage(__('The uploaded file contains invalid data.'));

        return $resultRedirect->setPath('*/*/');
    }
}
