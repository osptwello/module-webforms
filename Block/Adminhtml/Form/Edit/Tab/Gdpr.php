<?php

namespace VladimirPopov\WebForms\Block\Adminhtml\Form\Edit\Tab;

class Gdpr extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        array $data = []
    )
    {
        $this->_systemStore = $systemStore;
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('GDPR Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('GDPR Settings');
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

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

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

        $form->setFieldsetElementRenderer(
            $this->getLayout()->createBlock(
                'VladimirPopov\WebForms\Block\Adminhtml\Form\Renderer\Fieldset\Element',
                $this->getNameInLayout() . '_fieldset_element_renderer'
            )
        );
        $form->setDataObject($model);

        $form->setHtmlIdPrefix('form_');
        $form->setFieldNameSuffix('form');

        $fieldset = $form->addFieldset('webforms_gdpr', [
            'legend' => __('Personal Data Handling (GDPR) Settings')
        ]);

        $fieldset->addField('delete_submissions', 'select', [
            'label' => __('Do not store submissions'),
            'title' => __('Do not store submissions'),
            'name' => 'delete_submissions',
            'required' => false,
            'global' => true,
            'note' => __('Do not store submissions in the database. Just email them'),
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);

        $fieldset->addField('purge_enable', 'select', [
            'label' => __('Purge data periodically'),
            'title' => __('Purge data periodically'),
            'name' => 'purge_enable',
            'required' => false,
            'global' => true,
            'note' => __('Automatically delete submissions.<br><b>Requires Magento cron to be configured!</b><br><span style="color: red; font-weight: bold">Warning! Please be careful with this setting. The deleted data is not recoverable!</span>'),
            'options' => ['-1' => __('Default'), '1' => __('Yes'), '0' => __('No')],
        ]);

        $fieldset->addField('purge_period', 'text', [
            'label' => __('Purge period (days)'),
            'title' => __('Purge period (days)'),
            'name' => 'purge_period',
            'required' => false,
            'global' => true,
            'note' => __('Delete records older than specified period.<br>Overwritten with the default configuration value if it is enabled!'),
        ]);

        $fieldset = $form->addFieldset('webforms_gdpr_agreement', [
            'legend' => __('GDPR Agreement')
        ]);

        $fieldset->addField('show_gdpr_agreement_text', 'select', [
            'label' => __('Show GDPR agreement text'),
            'title' => __('Show GDPR agreement text'),
            'name' => 'show_gdpr_agreement_text',
            'required' => false,
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);

        $wysiwygConfig = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);

        $fieldset->addField('gdpr_agreement_text', 'editor', [
            'label' => __('GDPR agreement text'),
            'title' => __('GDPR agreement text'),
            'name' => 'gdpr_agreement_text',
            'note' => __('This text will be placed before submit button.<br>You can inform the customer if you are collecting his personal information and why'),
            'style' => 'height:20em;',
            'config' => $wysiwygConfig
        ]);

        $fieldset->addField('show_gdpr_agreement_checkbox', 'select', [
            'label' => __('Show GDPR agreement checkbox'),
            'title' => __('Show GDPR agreement checkbox'),
            'name' => 'show_gdpr_agreement_checkbox',
            'required' => false,
            'options' => ['1' => __('Yes'), '0' => __('No')],
        ]);

        $fieldset->addField('gdpr_agreement_checkbox_required', 'select', [
            'label' => __('Required'),
            'title' => __('Required'),
            'name' => 'gdpr_agreement_checkbox_required',
            'required' => false,
            'options' => [ '1' => __('Yes'), '0' => __('No')],
        ]);

        $fieldset->addField('gdpr_agreement_checkbox_do_not_store', 'select', [
            'label' => __('Don\'t store submission in the database if not checked'),
            'name' => 'gdpr_agreement_checkbox_do_not_store',
            'required' => false,
            'options' => [ '1' => __('Yes'), '0' => __('No')],
        ]);

        $fieldset->addField('gdpr_agreement_checkbox_label', 'text', [
            'label' => __('GDPR agreement checkbox label'),
            'title' => __('GDPR agreement checkbox label'),
            'name' => 'gdpr_agreement_checkbox_label',
        ]);

        $fieldset->addField('gdpr_agreement_checkbox_error_text', 'textarea', [
            'label' => __('GDPR agreement error text'),
            'title' => __('GDPR agreement error text'),
            'note' => __('This error will be displayed if the checkbox is required'),
            'name' => 'gdpr_agreement_checkbox_error_text',
        ]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
}