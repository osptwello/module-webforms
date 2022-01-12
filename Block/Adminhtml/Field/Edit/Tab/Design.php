<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Field\Edit\Tab;

use VladimirPopov\WebForms\Model\FieldFactory;

class Design extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /** @var FieldFactory */
    protected $fieldFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        FieldFactory $fieldFactory,
        array $data = []
    ) {
        $this->fieldFactory = $fieldFactory;

        parent::__construct($context,$registry,$formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Design');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Design');
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

        $fieldModel = $this->fieldFactory->create();


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

        $fieldset = $form->addFieldset('general', array(
            'legend' => __('General')
        ));

        $fieldset->addField('hide_label', 'select', array(
            'label' => __('Hide field label'),
            'name' => 'hide_label',
            'values' => ['1' => __('Yes'), '0' => __('No')]
        ));

        $fieldset->addField('inline_elements', 'select', array(
            'label' => __('Inline elements'),
            'name' => 'inline_elements',
            'note' => __('Display elements of the field such as radio or checkboxes inline instead of the column'),
            'values' => ['1' => __('Yes'), '0' => __('No')]
        ));

        $fieldset->addField('custom_attributes', 'text', array(
            'label' => __('Custom attributes'),
            'name' => 'custom_attributes',
            'note' => __('Specify custom input attributes here such as <i>readonly</i> or <i>disabled</i>'),
        ));

        $fieldset = $form->addFieldset('responsive', array(
            'legend' => __('Responsive Design')
        ));

        $fieldset->addField('width_lg', 'select', array(
            'label' => __('Large screen width'),
            'name' => 'width_lg',
            'note' => __('Proportion of the fieldset width for large size screen devices such as PC, Macbook, iMac etc.'),
            'values' => $fieldModel->getSizeValues(true)
        ));

        $fieldset->addField('width_md', 'select', array(
            'label' => __('Medium screen width'),
            'name' => 'width_md',
            'note' => __('Proportion of the fieldset width for medium size screen devices such as iPad, Galaxy Tab, Surface etc.'),
            'values' => $fieldModel->getSizeValues(true)
        ));

        $fieldset->addField('width_sm', 'select', array(
            'label' => __('Small screen width'),
            'name' => 'width_sm',
            'note' => __('Proportion of the fieldset width for small size screen devices such as iPhone, Galaxy, Pixel etc.'),
            'values' => $fieldModel->getSizeValues(true)
        ));

        $fieldset->addField('row_lg', 'select', array(
            'label' => __('Large screen start new row'),
            'name' => 'row_lg',
            'note' => __('Display the element in a new row. Use it to fix unwanted automatic element placement'),
            'values' => ["0" => __("No"), "1" => __("Yes")]
        ));

        $fieldset->addField('row_md', 'select', array(
            'label' => __('Medium screen start new row'),
            'name' => 'row_md',
            'note' => __('Display the element in a new row. Use it to fix unwanted automatic element placement'),
            'values' => ["0" => __("No"), "1" => __("Yes")]
        ));

        $fieldset->addField('row_sm', 'select', array(
            'label' => __('Small screen start new row'),
            'name' => 'row_sm',
            'note' => __('Display the element in a new row. Use it to fix unwanted automatic element placement'),
            'values' => ["0" => __("No"), "1" => __("Yes")]
        ));

        $fieldset = $form->addFieldset('css', array(
            'legend' => __('CSS')
        ));

        $fieldset->addField('css_class_container', 'text', array(
            'label' => __('CSS classes for the Container element'),
            'name' => 'css_class_container',
            'note' => __('Set CSS classes for the container element that holds Label and Input elements')
        ));

        $fieldset->addField('css_class', 'text', array(
            'label' => __('CSS classes for the Input element'),
            'name' => 'css_class',
            'note' => __('You can use it for additional field validation (see Prototype validation classes)')
        ));

        $fieldset->addField('css_style', 'text', array(
            'label' => __('Additional CSS style for the input element'),
            'name' => 'css_style',
            'note' => __('Add custom stylization to the input element')
        ));

        $fieldset = $form->addFieldset('result', array(
            'legend' => __('Results / Notifications')
        ));

        $fieldset->addField('result_display', 'select', array(
            'label' => __('Display field'),
            'title' => __('Display field'),
            'name' => 'result_display',
            'note' => __('Display field in result / notification messages'),
            'values' => $model->getDisplayOptions(),
        ));

        $fieldset = $form->addFieldset('browser', array(
            'legend' => __('Browser')
        ));

        $fieldset->addField('browser_autocomplete', 'text', array(
            'label' => __('Browser autocomplete'),
            'name' => 'browser_autocomplete',
            'note' => __('This attribute can be used across web-sites to pre-fill field with commonly used data such as name, email, telephone etc. This feature engages when user starts typing.')
        ));

        $this->_eventManager->dispatch('adminhtml_webforms_field_edit_tab_design_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
