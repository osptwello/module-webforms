<?php
/**
 * @author      Vladimir Popov
 * @copyright   Copyright © 2020 Vladimir Popov. All rights reserved.
 */

namespace VladimirPopov\WebForms\Block\Adminhtml;

use Magento\Backend\Block\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Config\Block\System\Config\Form\Fieldset;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\View\Helper\Js;
use Magento\Store\Model\ScopeInterface;
use VladimirPopov\WebForms\Helper\Data;

class License extends Fieldset
{
    protected $webformsHelper;

    protected $_metadata;

    public function __construct(
        Context $context,
        Session $authSession,
        Js $jsHelper,
        Data $webformsHelper,
        ProductMetadataInterface $metadata,
        array $data = []
    )
    {
        $this->webformsHelper = $webformsHelper;
        $this->_metadata      = $metadata;
        parent::__construct($context, $authSession, $jsHelper, $data);
    }

    final protected function _getHeaderHtml($element)
    {
        $html = parent::_getHeaderHtml($element);

        $html .= '<div id="webforms_license_messages" class="messages">';

        if (Data::DEV_CHECK) {
            if ($this->webformsHelper->isLocal()) {
                return $html . '<div class="message message-success success"><div data-ui-id="messages-message-success">' . __('Development environment detected. Serial number is not required.') . '</div></div></div>';
            }
        }
        $scopeType = ScopeInterface::SCOPE_STORE;
        $scopeCode = null;
        $websiteId = $this->getRequest()->getParam('website');
        if ($websiteId) {
            $scopeType = ScopeInterface::SCOPE_WEBSITE;
            $scopeCode = $websiteId;
        }
        $storeId = $this->getRequest()->getParam('store');
        if ($storeId) {
            $scopeCode = $storeId;
        }
        if (!$this->_scopeConfig->getValue('webforms/license/serial', $scopeType, $scopeCode)) {
            $html .= '<div class="message message-warning warning"><div data-ui-id="messages-message-warning">' . __('Please, enter serial number.') . '</div></div>';
        } else {
            $url  = $this->getUrl('webforms/license/verify', ['_current' => true]);
            $html .= '
            <script>
                require([\'jquery\'], function ($) {
                    $.ajax({
                        url: \'' . $url . '\',
                        dataType: \'json\',
                        error: function() {
                            $(\'#webforms_license_messages\').html(\'<div class="message message-warning warning"><div data-ui-id="messages-message-warning">' . __('Unknown error(s) occurred.') . '</div></div>\');
                        },
                        success: function(data) {
                            if(data.verified){
                                $(\'#webforms_license_messages\').html(\'<div class="message message-success success"><div data-ui-id="messages-message-success">' . __('License is active.') . '</div></div>\');
                            } else {
                                $(\'#webforms_license_messages\').html(\'\');
                            }
                            if(data.errors){
                                for(var i=0; i< data.errors.length; i++){
                                     $(\'#webforms_license_messages\').append(\'<div class="message message-error error"><div data-ui-id="messages-message-error">\' + data.errors[i] + \'</div></div>\');
                                }
                            }
                            if(data.warnings){
                                for(var i=0; i< data.warnings.length; i++){
                                     $(\'#webforms_license_messages\').append(\'<div class="message message-warning warning"><div data-ui-id="messages-message-warning">\' + data.warnings[i] + \'</div></div>\');
                                }
                            }
                        }
                    });
                });
            </script>
            ';
            $html .= '<div class="message"><div data-ui-id="messages-message-success">' . __('Connecting to license server...') . '</div data-ui-id="messages-message-success"></div>';

        }
        $html .= '</div>';
        return $html;
    }
}
