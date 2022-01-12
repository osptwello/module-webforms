<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Model\Plugin;

use Magento\Store\Model\ScopeInterface;

class ContactForm
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * @param \Magento\Contact\Block\ContactForm $contactForm
     */
    public function beforeToHtml(\Magento\Contact\Block\ContactForm $contactForm)
    {
        if ($this->_scopeConfig->getValue('webforms/contacts/enable', ScopeInterface::SCOPE_STORE)) {

            $contactForm->setTemplate('VladimirPopov_WebForms::webforms/contact/form.phtml');

            $template = 'VladimirPopov_WebForms::webforms/form/default.phtml';
            if ($this->_scopeConfig->getValue('webforms/contacts/template', ScopeInterface::SCOPE_STORE))
                $template = $this->_scopeConfig->getValue('webforms/contacts/template', ScopeInterface::SCOPE_STORE);

            $block = $contactForm->getLayout()->createBlock('VladimirPopov\WebForms\Block\Form', 'webforms.contact.form', [
                'data' => [
                    'webform_id' => $this->_scopeConfig->getValue('webforms/contacts/webform', ScopeInterface::SCOPE_STORE),
                    'template' => $template,
                    'after_submission_form' => $this->_scopeConfig->getValue('webforms/contacts/after_submission_form', ScopeInterface::SCOPE_STORE),
                    'scroll_to' => $this->_scopeConfig->getValue('webforms/contacts/scroll_to', ScopeInterface::SCOPE_STORE),
                    'async_load' => $this->_scopeConfig->getValue('webforms/contacts/async_load', ScopeInterface::SCOPE_STORE)
                ]
            ]);
            $contactForm->append($block);
        }
    }
}
