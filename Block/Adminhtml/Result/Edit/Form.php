<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Result\Edit;

use IntlDateFormatter;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Cms\Model\Wysiwyg\Config;
use Magento\Directory\Model\Config\Source\Country;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\System\Store;
use VladimirPopov\WebForms\Model\Field;
use function json_decode;

class Form extends Generic
{
    /**
     * @var Config
     */
    protected $_wysiwygConfig;

    /**
     * @var Store
     */
    protected $_systemStore;

    protected $_localeResolver;

    protected $_sourceCountry;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param Config $wysiwygConfig
     * @param Store $systemStore
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        Config $wysiwygConfig,
        Store $systemStore,
        ResolverInterface $localeResolver,
        Country $sourceCountry,
        array $data = []
    )
    {
        $this->_wysiwygConfig  = $wysiwygConfig;
        $this->_systemStore    = $systemStore;
        $this->_localeResolver = $localeResolver;
        $this->_sourceCountry  = $sourceCountry;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('result_form');
        $this->setTitle(__('Result Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model     = $this->_coreRegistry->registry('webforms_result');
        $modelForm = $this->_coreRegistry->registry('webforms_form');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => [
                'id' => 'edit_form',
                'action' => $this->getUrl('*/*/save', ['_current' => true]),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            ]]
        );

        $form->setFieldNameSuffix('result');

        if ($model->getId())
            $fieldset = $form->addFieldset('result_info', array('legend' => __('Result # %1', $model->getId())));
        else
            $fieldset = $form->addFieldset('result_info', array('legend' => __('New Result')));

        $customer_id = $model->getCustomerId();
        $model->setCustomer($customer_id);
        $customer_ip = long2ip($model->getData('customer_ip'));

        $model->addData(array(
            'info_customer_ip' => $customer_ip,
            'info_created_time' => $this->_localeDate->formatDate($model->getCreatedTime(), IntlDateFormatter::MEDIUM, true),
            'info_webform_name' => $modelForm->getName(),
        ));


        $fieldset->addField('info_webform_name', 'link', array(
            'id' => 'info_webform_name',
            'class' => 'control-value special',
            'href' => $this->getUrl('*/form/edit', array('id' => $modelForm->getId())),
            'label' => __('Web-form'),
        ));

        if ($model->getId())
            $fieldset->addField('info_created_time', 'label', array(
                'id' => 'info_created_time',
                'bold' => true,
                'label' => __('Result Date'),
            ));

        $fieldset->addType('customer', 'VladimirPopov\WebForms\Block\Adminhtml\Result\Element\Customer');

        $fieldset->addField(
            'customer', 'customer',
            array(
                'label' => __('Customer'),
                'name' => 'customer',
            )
        );

        $fieldset->addField(
            'store_id', 'select',
            array(
                'name' => 'store_id',
                'label' => __('Store View'),
                'values' => $this->_systemStore->getStoreValuesForForm(false, false),
                'required' => true,
            )
        );

        if ($model->getId()) {
            if ($this->_scopeConfig->getValue('webforms/gdpr/collect_customer_ip', ScopeInterface::SCOPE_STORE, $model->getStoreId()))
                $fieldset->addField('info_customer_ip', 'label', array(
                    'id' => 'info_customer_ip',
                    'bold' => true,
                    'label' => __('Sent from IP'),
                ));
        }
        foreach ($modelForm->_getHidden() as $hiddenField) {
            $fieldset->addField('hiddenfield_' . $hiddenField->getId(), 'label', [
                'label' => $hiddenField->getName(),
                'name' => 'hiddenField[' . $hiddenField->getId() . ']',
                'value' => nl2br($model->getData('field_'.$hiddenField->getId()))
            ]);
        }

        $wysiwygConfig = $this->_wysiwygConfig->getConfig();

        $fields_to_fieldsets = $modelForm->getFieldsToFieldsets(true);

        foreach ($fields_to_fieldsets as $fs_id => $fs_data) {
            $legend = "";
            if (!empty($fs_data['name'])) $legend = $fs_data['name'];

            // check logic visibility
            $fieldset = $form->addFieldset('fs_' . $fs_id, array(
                'legend' => $legend,
                'fieldset_container_id' => 'fieldset_' . $fs_id . '_container'
            ));

            foreach ($fs_data['fields'] as $field) {
                $type   = 'text';
                $config = array
                (
                    'name' => 'field[' . $field->getId() . ']',
                    'label' => $field->getName(),
                    'container_id' => 'field_' . $field->getId() . '_container',
                    'required' => $field->getRequired()
                );

                /** @var Field $field */
                switch ($field->getType()) {
                    case 'textarea':
                        $type = 'textarea';
                        break;
                    case 'password':
                        $type = 'password';
                        break;

                    case 'hidden':
                        $type = 'text';
                        break;

                    case 'wysiwyg':
                        $type             = 'editor';
                        $config['config'] = $wysiwygConfig;
                        break;

                    case 'date':
                    case 'date/dob':
                        $type                  = 'date';
                        $config['date_format'] = $field->getDateFormat();
                        break;

                    case 'datetime':
                        $type                  = 'date';
                        $config['time']        = true;
                        $config['date_format'] = $field->getDateFormat();
                        $config['time_format'] = $field->getTimeFormat();
                        break;

                    case 'select/radio':
                        $type               = 'select';
                        $config['required'] = false;
                        $config['values']   = $field->getOptionsArray();
                        break;

                    case 'select/checkbox':
                        $type  = 'checkboxes';
                        $value = explode("\n", $model->getData('field_' . $field->getId()));
                        $model->setData('field_' . $field->getId(), $value);
                        $config['options'] = $field->getSelectOptions();
                        $config['name']    = 'field[' . $field->getId() . '][]';
                        break;

                    case 'select':
                        $type             = 'select';
                        $config['values'] = $field->getSelectValues();
                        if ($field->getValue('multiselect')) {
                            $type  = 'multiselect';
                            $value = explode("\n", $model->getData('field_' . $field->getId()));
                            $model->setData('field_' . $field->getId(), $value);
                            $config['values'] = $field->getOptionsArray();
                        }
                        break;

                    case 'subscribe':
                        $type              = 'select';
                        $config['options'] = ['1' => __('Yes'), '0' => __('No')];
                        break;

                    case 'select/contact':
                        $type             = 'select';
                        $config['values'] = $field->getSelectValues(false);
                        break;

                    case 'stars':
                        $type              = 'select';
                        $config['options'] = $field->getStarsOptions();
                        break;

                    case 'file':
                        $type = 'file';

                        $config['dropzone']              = $field->getValue('dropzone');
                        $config['dropzone_url']          = $this->_storeManager->getStore()->getUrl('webforms/file/dropzone');
                        $config['dropzone_name']         = $config['name'];
                        $config['dropzone_text']         = $field->getValue('dropzone_text');
                        $config['dropzone_maxfiles']     = $field->getValue('dropzone_maxfiles');
                        $config['allowed_size']          = $modelForm->getUploadLimit($field->getType());
                        $config['allowed_extensions']    = $field->getAllowedExtensions();
                        $config['restricted_extensions'] = $field->getRestrictedExtensions();

                        $config['dropzone_url'] = $this->getUrl('webforms/file/dropzone');
                        $config['field_id']     = $field->getId();
                        $config['result_id']    = $model->getId();
                        $config['url']          = $model->getFilePath($field->getId());
                        $config['name']         = 'file_' . $field->getId();


                        break;

                    case 'image':
                        $type = 'image';

                        $config['dropzone']              = $field->getValue('dropzone');
                        $config['dropzone_url']          = $this->_storeManager->getStore()->getUrl('webforms/file/dropzone');
                        $config['dropzone_name']         = $config['name'];
                        $config['dropzone_text']         = $field->getValue('dropzone_text');
                        $config['dropzone_maxfiles']     = $field->getValue('dropzone_maxfiles');
                        $config['allowed_size']          = $modelForm->getUploadLimit($field->getType());
                        $config['allowed_extensions']    = $field->getAllowedExtensions();
                        $config['restricted_extensions'] = $field->getRestrictedExtensions();

                        $config['field_id']  = $field->getId();
                        $config['result_id'] = $model->getId();
                        $config['url']       = $model->getFilePath($field->getId());
                        $config['name']      = 'file_' . $field->getId();

                        break;

                    case 'html':
                        $type                         = 'label';
                        $config['label']              = false;
                        $config['after_element_html'] = $field->getValue('html');
                        break;

                    case 'country':
                        $type             = 'select';
                        $config['values'] = $this->_sourceCountry->toOptionArray();
                        break;

                    case 'colorpicker':
                        $type             = 'colorpicker';
                        break;

                    case 'region':
                        $value = json_decode($model->getData('field_' . $field->getId()), true);
                        $config['country_field_id'] = 'field_'.$field->getValue('region_country_field_id');
                        $config['region'] = !empty($value['region']) ? $value['region'] : '';
                        $config['region_id'] = !empty($value['region_id']) ? $value['region_id'] : '';
                        $type = 'region';
                        break;
                }
                $config['type'] = $type;
                $config         = new DataObject($config);
                $fieldset->addType('image', 'VladimirPopov\WebForms\Block\Adminhtml\Result\Element\Image');
                $fieldset->addType('file', 'VladimirPopov\WebForms\Block\Adminhtml\Result\Element\File');
                $fieldset->addType('region', 'VladimirPopov\WebForms\Block\Adminhtml\Result\Element\Region');
                $fieldset->addType('colorpicker', 'VladimirPopov\WebForms\Block\Adminhtml\Result\Element\Colorpicker');

                $this->_eventManager->dispatch('webforms_block_adminhtml_results_edit_form_prepare_layout_field', array('form' => $form, 'fieldset' => $fieldset, 'field' => $field, 'config' => $config));
                $fieldset->addField('field_' . $field->getId(), $config->getData('type'), $config->getData());
            }
        }

        foreach ($modelForm->_getHidden() as $hiddenField) {
            $form->addField('field_' . $hiddenField->getId(), 'hidden', [
                'name' => 'field[' . $hiddenField->getId() . ']'
            ]);
        }

        $form->addValues($model->getData());

        $form->addField('result_id', 'hidden', array
        (
            'name' => 'result_id',
            'value' => $model->getId(),
        ));

        $form->addField('webform_id', 'hidden', array
        (
            'name' => 'webform_id',
            'value' => $modelForm->getId(),
        ));

        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
