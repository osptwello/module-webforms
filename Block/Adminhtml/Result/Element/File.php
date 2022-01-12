<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml\Result\Element;

use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use VladimirPopov\WebForms\Model;

class File extends \Magento\Framework\Data\Form\Element\AbstractElement
{
    protected $_resultFactory;

    protected $fileCollectionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        Model\ResultFactory $resultFactory,
        Model\ResourceModel\File\CollectionFactory $fileCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        $data = []
    ) {
        parent::__construct($factoryElement, $factoryCollection, $escaper, $data);
        $this->setType('file');
        $this->setExtType('file');
        if (isset($data['value'])) {
            $this->setValue($data['value']);
        }
        $this->fileCollectionFactory = $fileCollectionFactory;
        $this->_resultFactory = $resultFactory;
        $this->_scopeConfig = $scopeConfig;
    }

    public function _getName(){
        return "file_{$this->getData('field_id')}";
    }

    public function removeClass($class)
    {
        $classes = array_unique(explode(' ', $this->getClass()));
        if (false !== ($key = array_search($class, $classes))) {
            unset($classes[$key]);
        }
        $this->setClass(implode(' ', $classes));
        return $this;
    }

    public function getElementHtml()
    {
        $this->addClass('input-file');
        if ($this->getRequired()) {
            $this->removeClass('required-entry');
        }

        $element = sprintf('<input id="%s" name="%s" %s />%s',
            $this->getHtmlId(),
            $this->_getName(),
            $this->serialize($this->getHtmlAttributes()),
            $this->getAfterElementHtml()
        );

        return $this->_getPreviewHtml() . $element . $this->_getDropzoneHtml();
    }

    protected function _getPreviewHtml(){
        $html = '';
        if ($this->getData('result_id')) {
            $result = $this->_resultFactory->create()->load($this->getData('result_id'));
            $field_id = $this->getData('field_id');
            $files = $this->fileCollectionFactory->create()
                ->addFilter('result_id', $result->getId())
                ->addFilter('field_id', $field_id);
            if (count($files)) {
                $html .= '<div class="webforms-file-pool">';
                if(count($files) > 1)
                    $html .= $this->_getSelectAllHtml();
                /** @var \VladimirPopov\WebForms\Model\File $file */
                foreach ($files as $file) {
                    $nameStart = '<div class="webforms-file-link-name">' . mb_substr($file->getName(), 0, mb_strlen($file->getName()) - 7) . '</div>';
                    $nameEnd = '<div class="webforms-file-link-name-end">' . mb_substr($file->getName(), -7) . '</div>';

                    $html .= '<div class="webforms-file-cell">';

                    if (file_exists($file->getFullPath())) {
                        $html .= '<nobr><a class="grid-button-action webforms-file-link" href="' . $file->getDownloadLink(true) . '">' . $nameStart . $nameEnd . ' <span>[' . $file->getSizeText() . ']</span></a></nobr>';
                    }

                    $html .= $this->_getDeleteCheckboxHtml($file);

                    $html .= '</div>';

                }
                $html .= '</div>';
            }
        }

        return $html;
    }

    protected function _getSelectAllHtml()
    {
        $id = $this->getHtmlId() . 'selectall';
        $html = '';
        $html .= '<script>function checkAll(elem){elem.up().up().select("input[type=checkbox]").invoke("writeAttribute","checked",elem.checked);}</script>';
        $html .= '<div class="webforms-file-pool-selectall"><input id="' . $id . '" type="checkbox" class="webforms-file-delete-checkbox" onchange="checkAll(this)"/> <label for="' . $id . '">' . __('Select All') . '</label></div>';
        return $html;
    }

    public function getDropzoneName()
    {
        $name = $this->getData('dropzone_name');
        if ($suffix = $this->getForm()->getFieldNameSuffix()) {
            $name = $this->getForm()->addSuffixToName($name, $suffix);
        }
        return $name;
    }

    protected function _getDropzoneHtml()
    {
        $config = array();

        $config['url'] = $this->getData('dropzone_url');
        $config['fieldId'] = $this->getHtmlId();
        $config['fieldName'] = $this->getDropzoneName();
        $config['dropZone'] = $this->getData('dropzone') ? 1 : 0;
        $config['dropZoneText'] = $this->getData('dropzone_text') ? $this->getData('dropzone_text') : __('Add files or drop here');
        $config['maxFiles'] = $this->getData('dropzone_maxfiles') ? $this->getData('dropzone_maxfiles') : 5;
        $config['allµowedSize'] = $this->getData('allowed_size');
        $config['allowedExtensions'] = $this->getData('allowed_extensions');
        $config['restrictedExtensions'] = $this->getData('restricted_extensions');
        $config['validationCssClass'] = '';
        $config['errorMsgAllowedExtensions'] = __('Selected file has none of allowed extensions: %s');
        $config['errorMsgRestrictedExtensions'] = __('Uploading of potentially dangerous files is not allowed.');
        $config['errorMsgAllowedSize'] = __('Selected file exceeds allowed size: %s kB');
        $config['errorMsgUploading'] = __('Error uploading file');
        $config['errorMsgNotReady'] = __('Please wait... the upload is in progress.');

        return '<script>require([\'VladimirPopov_WebForms/js/dropzone\'], function (JsWebFormsDropzone) {new JsWebFormsDropzone(' . json_encode($config) . ')})</script>';

    }

    protected function _getDeleteCheckboxHtml($file)
    {
        $html = '';
        if ($file) {
            $checkboxId = 'delete_file_' . $file->getId();
            $checkboxName = str_replace('file_', 'delete_file_', $this->getName()) . '[]';

            $checkbox = array(
                'type' => 'checkbox',
                'name' => $checkboxName,
                'value' => $file->getLinkHash(),
                'class' => 'webforms-file-delete-checkbox',
                'id' => $checkboxId
            );

            $label = array(
                'for' => $checkboxId
            );

            $html .= '<p>';
            $html .= $this->_drawElementHtml('input', $checkbox) . ' ';
            $html .= $this->_drawElementHtml('label', $label, false) . $this->_getDeleteCheckboxLabel() . '</label>';
            $html .= '</p>';
        }
        return $html;
    }

    protected function _getDeleteCheckboxSpanClass()
    {
        return 'delete-file';
    }

    protected function _getDeleteCheckboxLabel()
    {
        return __('Delete File');
    }

    protected function _drawElementHtml($element, array $attributes, $closed = true)
    {
        $parts = array();
        foreach ($attributes as $k => $v) {
            $parts[] = sprintf('%s="%s"', $k, $v);
        }

        return sprintf('<%s %s%s>', $element, implode(' ', $parts), $closed ? ' /' : '');
    }

}
