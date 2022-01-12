<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Field\Edit\Tab;

class Validation extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Validation');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Validation');
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
        /* @var $model \VladimirPopov\WebForms\Model\Field */
        $model = $this->_coreRegistry->registry('webforms_field');

        /* @var $model \VladimirPopov\WebForms\Model\Form */
        $modelForm = $this->_coreRegistry->registry('webforms_form');

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_isAllowedAction('VladimirPopov_WebForms::manage_forms')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'VladimirPopov\WebForms\Block\Adminhtml\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element_renderer'
            )
        );
        $form->setDataObject($model);

        $form->setHtmlIdPrefix('field_');
        $form->setFieldNameSuffix('field');

        $fieldset = $form->addFieldset('webforms_unique', [
            'legend' => __('Unique Value')
        ]);

        $fieldset->addField('validate_unique', 'select', [
            'label' => __('Unique value'),
            'name' => 'validate_unique',
            'options' => ['1' => __('Yes'), '0' => __('No')],
            'note' => __('Validate input value against previously submitted data')
        ]);

        $fieldset->addField('validate_unique_message', 'textarea', [
            'label' => __('Unique field validation message'),
            'name' => 'validate_unique_message',
            'note' => __('Displayed error message text if unique value validation fails')
        ]);

        $fieldset = $form->addFieldset('webforms_length', [
            'legend' => __('Length')
        ]);

        $fieldset->addField('validate_length_min', 'text', [
            'label' => __('Minimum length'),
            'class' => 'validate-number',
            'name' => 'validate_length_min',
        ]);

        $fieldset->addField('validate_length_min_message', 'textarea', [
            'label' => __('Minimum length error message'),
            'name' => 'validate_length_min_message',
        ]);

        $fieldset->addField('validate_length_max', 'text', [
            'label' => __('Maximum length'),
            'class' => 'validate-number',
            'name' => 'validate_length_max',
        ]);

        $fieldset->addField('validate_length_max_message', 'textarea', [
            'label' => __('Maximum length error message'),
            'name' => 'validate_length_max_message',
        ]);

        $fieldset = $form->addFieldset('webforms_regex', [
            'legend' => __('Regular Expression')
        ]);

        $fieldset->addField('validate_regex', 'text', [
            'label' => __('Validation RegEx'),
            'name' => 'validate_regex',
            'note' => __('Validate with custom regular expression')
        ]);

        $fieldset->addField('validate_message', 'textarea', [
            'label' => __('Validation error message'),
            'name' => 'validate_message',
            'note' => __('Displayed error message text if regex validation fails')
        ]);

        if ($model->getData('validate_length_min') == 0) {
            $model->setData('validate_length_min', '');
        }

        if ($model->getData('validate_length_max') == 0) {
            $model->setData('validate_length_max', '');
        }

        $this->_eventManager->dispatch('adminhtml_webforms_field_edit_tab_validation_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
