<?php

namespace VladimirPopov\WebForms\Controller\Result;

use Exception;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use VladimirPopov\WebForms\Controller\ResultAction;

/**
 * Class Delete
 * @package VladimirPopov\WebForms\Controller\Result
 */
class Delete extends ResultAction
{
    /**
     * @return ResponseInterface|ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        $this->_init();
        $webform = $this->_result->getWebform();
        if (!in_array('delete', $webform->getCustomerResultPermissions())) $this->_redirect('customer/account');

        $this->_result->delete();
        $this->messageManager->addSuccess(__('The record has been deleted.'));
        $this->_redirect('webforms/customer/account', ['webform_id' => $webform->getId()]);
    }
}