<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Controller\Adminhtml\Form;

use Magento\Authorization\Model\RulesFactory;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Authorization;


class Save extends \Magento\Backend\App\Action
{
    protected $roleLocator;

    protected $_rulesFactory;

    protected $_rulesCollectionFactory;

    protected $_aclBuilder;

    protected $_cache;


    protected $formFactory;

    protected $fieldFactory;

    protected $fieldsetFactory;

    public function __construct(
        Action\Context $context,
        Authorization\RoleLocator $roleLocator,
        RulesFactory $rulesFactory,
        \Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory $rulesCollectionFactory,
        \Magento\Framework\Acl\Builder $aclBuilder,
        \VladimirPopov\WebForms\Model\FormFactory $formFactory,
        \VladimirPopov\WebForms\Model\FieldFactory $fieldFactory,
        \VladimirPopov\WebForms\Model\FieldsetFactory $fieldsetFactory
    )
    {
        $this->roleLocator             = $roleLocator;
        $this->_rulesFactory           = $rulesFactory;
        $this->_rulesCollectionFactory = $rulesCollectionFactory;
        $this->_aclBuilder             = $aclBuilder;
        $this->formFactory             = $formFactory;
        $this->fieldFactory            = $fieldFactory;
        $this->fieldsetFactory         = $fieldsetFactory;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        if ($this->getRequest()->getParam('id')) {
            $collection = $this->_rulesCollectionFactory->create()
                ->addFilter('role_id', $this->roleLocator->getAclRoleId())
                ->addFilter('resource_id', 'VladimirPopov_WebForms::form' . $this->getRequest()->getParam('id'))
                ->addFilter('permission', 'allow');
            if ($collection->count() === 0) return false;
            return true;
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
        $data  = $this->getRequest()->getPostValue('form');
        $store = $this->getRequest()->getParam('store');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $model = $this->formFactory->create();

            $id = empty($data['id']) ? false : $data['id'];

            if ($id) {
                $model->load($id);
            }

            $this->_eventManager->dispatch(
                'webforms_form_prepare_save',
                ['form' => $model, 'request' => $this->getRequest()]
            );

            try {
                // update fields position
                $fieldsData = $this->getRequest()->getParam('fields_position');
                if (is_array($fieldsData)) {
                    foreach ($fieldsData['position'] as $field_id => $position) {
                        $this->fieldFactory->create()
                            ->setId($field_id)
                            ->setPosition($position)
                            ->save();
                    }
                }
                $model->updateFieldPositions();

                // update fields width
                $fieldsData = $this->getRequest()->getParam('fields_width');

                if (is_array($fieldsData)) {
                    if (!empty($fieldsData['width_lg']))
                        foreach ($fieldsData['width_lg'] as $field_id => $width_lg) {
                            $this->fieldFactory->create()
                                ->setId($field_id)
                                ->setData('width_lg', $width_lg)
                                ->save();
                        }

                    if (!empty($fieldsData['width_md']))
                        foreach ($fieldsData['width_md'] as $field_id => $width_md) {
                            $this->fieldFactory->create()
                                ->setId($field_id)
                                ->setData('width_md', $width_md)
                                ->save();
                        }

                    if (!empty($fieldsData['width_sm']))
                        foreach ($fieldsData['width_sm'] as $field_id => $width_sm) {
                            $this->fieldFactory->create()
                                ->setId($field_id)
                                ->setData('width_sm', $width_sm)
                                ->save();
                        }
                }

                // update fieldsets position
                $fieldsetsData = $this->getRequest()->getParam('fieldsets_position');
                if (is_array($fieldsetsData)) {
                    foreach ($fieldsetsData['position'] as $fieldset_id => $position) {
                        $this->fieldsetFactory->create()
                            ->setId($fieldset_id)
                            ->setPosition($position)
                            ->save();
                    }
                }
                $model->updateFieldsetPositions();

                // update fieldsets width
                $fieldsetsData = $this->getRequest()->getParam('fieldsets_width');
                if (is_array($fieldsetsData)) {
                    if (!empty($fieldsetsData['width_lg']))
                        foreach ($fieldsetsData['width_lg'] as $fieldset_id => $width_lg) {
                            $this->fieldsetFactory->create()
                                ->setId($fieldset_id)
                                ->setData('width_lg', $width_lg)
                                ->save();
                        }

                    if (!empty($fieldsetsData['width_md']))
                        foreach ($fieldsetsData['width_md'] as $fieldset_id => $width_md) {
                            $this->fieldsetFactory->create()
                                ->setId($fieldset_id)
                                ->setData('width_md', $width_md)
                                ->save();
                        }

                    if (!empty($fieldsetsData['width_sm']))
                        foreach ($fieldsetsData['width_sm'] as $fieldset_id => $width_sm) {
                            $this->fieldsetFactory->create()
                                ->setId($fieldset_id)
                                ->setData('width_sm', $width_sm)
                                ->save();
                        }
                }

                if ($store) {
                    // fix for access groups saving empty store view value
                    $use_default = $this->getRequest()->getParam('use_default');
                    foreach ($data as $key => $value) {
                        if(in_array('form['.$key.'][]', $use_default)){
                            unset($data[$key]);
                        }
                    }
                    $model->saveStoreData($store, $data);
                } else
                    $model->setData($data)->save();

                // update role permissions
                if (!$this->_authorization->isAllowed('Magento_Backend::all')) {
                    $collection = $this->_rulesCollectionFactory->create()
                        ->addFilter('role_id', $this->roleLocator->getAclRoleId())
                        ->addFilter('resource_id', 'VladimirPopov_WebForms::form' . $model->getId())
                        ->addFilter('permission', 'allow');
                    if ($collection->count() === 0) {
                        $this->_rulesFactory->create()->setData([
                            'role_id' => $this->roleLocator->getAclRoleId(),
                            'resource_id' => 'VladimirPopov_WebForms::form' . $model->getId(),
                            'permission' => 'allow'
                        ])->save();
                    }
                }

                $this->messageManager->addSuccessMessage(__('You saved this form.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the form.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
