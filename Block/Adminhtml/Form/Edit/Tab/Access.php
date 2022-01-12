<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab;


use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use VladimirPopov\WebForms\Model\Config\Result\Permission as ResultPermission;

/**
 * Class Access
 * @package VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab
 */
class Access extends Generic implements TabInterface
{
    /**
     * @var CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var ResultPermission
     */
    protected $resultPermission;

    /**
     * Access constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param CollectionFactory $groupCollectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        CollectionFactory $groupCollectionFactory,
        ResultPermission $resultPermission,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->resultPermission       = $resultPermission;
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Access Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Access Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Prepare form
     *
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('webforms_form');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('Vladimipopov_WebForms::form_save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var Form $form */
        $form = $this->_formFactory->create();

        $form->setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'VladimirPopov\WebForms\Block\Adminhtml\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element_renderer'
            )
        );
        $form->setDataObject($model);

        $form->setHtmlIdPrefix('form_');
        $form->setFieldNameSuffix('form');

        $fieldset = $form->addFieldset('customer_access', [
            'legend' => __('Customer Access')
        ]);

        $access_enable = $fieldset->addField('access_enable', 'select', [
            'name' => 'access_enable',
            'label' => __('Limit customer access'),
            'note' => __('Limit access to the form for certain customer groups'),
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);

        $access_groups = $fieldset->addField('access_groups', 'multiselect', [
            'label' => __('Allowed customer groups'),
            'title' => __('Allowed customer groups'),
            'name' => 'access_groups',
            'required' => false,
            'note' => __('Allow form access for selected customer groups only'),
            'values' => $this->getGroupOptions(),
        ]);

        $fieldset = $form->addFieldset('customer_dashboard', [
            'legend' => __('Customer Dashboard')
        ]);

        $dashboard_enable = $fieldset->addField('dashboard_enable', 'select', [
            'name' => 'dashboard_enable',
            'label' => __('Add form to customer dashboard'),
            'note' => __('Add link to the form and submission results to customer dashboard menu'),
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);

        $dashboard_groups = $fieldset->addField('dashboard_groups', 'multiselect', [
            'label' => __('Customer groups'),
            'title' => __('Customer groups'),
            'name' => 'dashboard_groups',
            'required' => false,
            'note' => __('Add form to dashboard for selected customer groups only'),
            'values' => $this->getGroupOptions(),
        ]);

        $customer_result_permissions = $fieldset->addField('customer_result_permissions', 'multiselect', [
            'name' => 'customer_result_permissions',
            'label' => __('Result permissions'),
            'note' => __('Permissions identify which actions customer can perform with results in the dashboard area'),
            'values' => $this->resultPermission->toOptionArray(),
        ]);


        $fieldset = $form->addFieldset('file_access', [
            'legend' => __('File Access')
        ]);

        $frontend_download = $fieldset->addField('frontend_download', 'select', [
            'name' => 'frontend_download',
            'label' => __('Allow frontend file downloads'),
            'note' => __('Adds file links to admin notification emails. Useful when you can not attach large files to email but need to be able to download them directly from the email program. Its recommended to turn it off for sensitive data.'),
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);


        $this->_eventManager->dispatch('adminhtml_webforms_form_edit_tab_access_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * @return array
     */
    public function getGroupOptions()
    {
        $group_options = $this->groupCollectionFactory->create()->toOptionArray();
        $options       = [];
        foreach ($group_options as $group) {
            if ($group['value'] > 0) $options[] = $group;
        }
        return $options;
    }
}
