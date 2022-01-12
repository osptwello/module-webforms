<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Script;

use Magento\Framework\View\Element\Template;

class Gdpr extends \Magento\Framework\View\Element\Template
{
    /** @var \VladimirPopov\WebForms\Model\Form */
    protected $_form;

    protected $_template = 'VladimirPopov_WebForms::webforms/scripts/gdpr.phtml';

    protected $_filterProvider;

    /**
     * Internal constructor, that is called from real constructor
     * @return void
     */
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        Template\Context $context,
        array $data = []
    )
    {
        parent::__construct($context, $data);

        $this->_filterProvider = $filterProvider;
    }

    public function setForm(\VladimirPopov\WebForms\Model\Form $form)
    {
        $this->_form = $form;
        return $this;
    }

    public function getForm()
    {
        return $this->_form;
    }

    protected function _toHtml()
    {
        $this->setData('show_agreement_text', $this->_form->getData('show_gdpr_agreement_text'));
        if($this->_form->getData('gdpr_agreement_text')) {
            $this->setData('agreement_text', $this->_filterProvider->getPageFilter()->filter($this->_form->getData('gdpr_agreement_text')));
        }
        $this->setData('show_checkbox', $this->_form->getData('show_gdpr_agreement_checkbox'));
        $this->setData('checkbox_required', $this->_form->getData('gdpr_agreement_checkbox_required'));
        $this->setData('checkbox_label', $this->_form->getData('gdpr_agreement_checkbox_label'));
        $this->setData('error_text', $this->_form->getData('gdpr_agreement_checkbox_error_text'));

        return parent::_toHtml();
    }
}
