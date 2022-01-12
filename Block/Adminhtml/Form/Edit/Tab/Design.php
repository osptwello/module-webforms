<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab;

class Design extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Design Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Design Settings');
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

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setDataObject($model);

        $form->setHtmlIdPrefix('form_');
        $form->setFieldNameSuffix('form');

        $fieldset = $form->addFieldset('responsive_design', [
            'legend' => __('Responsive Design')
        ]);

        $show_width_lg = $fieldset->addField('show_width_lg', 'select', [
            'name' => 'show_width_lg',
            'global' => true,
            'label' => __('Large screen width controls'),
            'note' => __('Show large screen width controls in Fieldsets and Fields tab'),
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);

        $show_width_md = $fieldset->addField('show_width_md', 'select', [
            'name' => 'show_width_md',
            'global' => true,
            'label' => __('Medium screen width controls'),
            'note' => __('Show medium screen width controls in Fieldsets and Fields tab'),
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);

        $show_width_sm = $fieldset->addField('show_width_sm', 'select', [
            'name' => 'show_width_sm',
            'global' => true,
            'label' => __('Small screen width controls'),
            'note' => __('Show small screen width controls in Fieldsets and Fields tab'),
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);

        $this->_eventManager->dispatch('adminhtml_webforms_form_edit_tab_design_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

}
