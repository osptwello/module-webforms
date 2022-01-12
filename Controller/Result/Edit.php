<?php

namespace VladimirPopov\WebForms\Controller\Result;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;
use VladimirPopov\WebForms\Controller\ResultAction;

/**
 * Class Edit
 * @package VladimirPopov\WebForms\Controller\Result
 */
class Edit extends ResultAction
{
    /**
     * @return ResponseInterface|ResultInterface|Page|void
     */
    public function execute()
    {
        $this->_init();
        $webform = $this->_result->getWebform();
        if (!in_array('edit', $webform->getCustomerResultPermissions())) $this->_redirect('webforms/customer/account', ['webform_id' => $webform->getId()]);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getLayout()->getBlock('webforms_customer_account_form_edit')
            ->setData('webform_id', $webform->getId())
            ->setResult($this->_result);
        $resultPage->getConfig()->getTitle()->set($this->_result->getEmailSubject());

        return $resultPage;
    }
}